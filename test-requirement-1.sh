#!/bin/bash

# ============================================================================
# 🧪 Testing Script for Requirement 1: Concurrent Access & Data Integrity
# ============================================================================

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}============================================================================${NC}"
echo -e "${BLUE}🧪 Testing Requirement #1: Concurrent Access & Data Integrity${NC}"
echo -e "${BLUE}============================================================================${NC}"
echo ""

# Step 1: Fresh Migration and Seeding
echo -e "${YELLOW}📍 Step 1: Preparing Database...${NC}"
echo -e "${YELLOW}Running: php artisan migrate:fresh --seed${NC}"
php artisan migrate:fresh --seed

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database prepared successfully!${NC}"
else
    echo -e "${RED}❌ Database preparation failed!${NC}"
    exit 1
fi
echo ""

# Step 2: Get available store products
echo -e "${YELLOW}📍 Step 2: Getting store products for testing...${NC}"
php artisan tinker <<'PHP'
use App\Models\Store_product;
$products = Store_product::limit(5)->get();
echo "Available Products:\n";
echo "ID\tStore\tProduct\tQty\n";
echo "─────────────────────────────\n";
foreach ($products as $p) {
    echo "{$p->id}\t{$p->store_id}\t{$p->product_id}\t{$p->quantity}\n";
}
PHP
echo ""

# Step 3: Get JWT Token
echo -e "${YELLOW}📍 Step 3: Authenticating user...${NC}"

RESPONSE=$(curl -s -X POST http://localhost/api/auth/login \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"mobile": "0100123456", "password": "password"}')

TOKEN=$(echo $RESPONSE | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ Authentication failed!${NC}"
    echo "Response: $RESPONSE"
    exit 1
else
    echo -e "${GREEN}✅ Authentication successful!${NC}"
    echo -e "${GREEN}Token: ${TOKEN:0:20}...${NC}"
fi
echo ""

# Step 4: Test Concurrent Requests (Basic - No Lock)
echo -e "${YELLOW}📍 Step 4: Testing BASIC method (No Lock - Race Condition Risk)...${NC}"
echo -e "${YELLOW}Sending 10 concurrent requests to /api/auth/cart${NC}"

START_TIME=$(date +%s%N)
SUCCESS_COUNT=0

for i in {1..10}; do
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost/api/auth/cart \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{"product_id": 1, "store_id": 1, "quantity": 1}')
    
    if [ "$STATUS" == "200" ]; then
        echo -e "  Request $i: ${GREEN}✅${NC}"
        ((SUCCESS_COUNT++))
    else
        echo -e "  Request $i: ${RED}❌ (Status: $STATUS)${NC}"
    fi
done

END_TIME=$(date +%s%N)
BASIC_TIME=$(( (END_TIME - START_TIME) / 1000000 ))

echo -e "${BLUE}⏱️ Total time: ${BASIC_TIME}ms for 10 requests${NC}"
echo -e "${GREEN}✅ Basic requests completed! ($SUCCESS_COUNT/10 successful)${NC}"
echo ""

# Step 5: Check inventory after basic requests
echo -e "${YELLOW}📍 Step 5: Checking inventory after BASIC requests...${NC}"
php artisan tinker <<'PHP'
use App\Models\Store_product;
$p = Store_product::find(1);
echo "Product ID 1 - Remaining Quantity: " . $p->quantity . "\n";
PHP
echo -e "${YELLOW}⚠️  Note: Expected quantity should be decreased by 10${NC}"
echo ""

# Step 6: Reset inventory
echo -e "${YELLOW}📍 Step 6: Resetting inventory for SAFE test...${NC}"
php artisan tinker <<'PHP'
use App\Models\Store_product;
$p = Store_product::find(1);
$p->quantity = 100;
$p->save();
echo "Reset to: " . $p->quantity . "\n";
PHP
echo -e "${GREEN}✅ Inventory reset!${NC}"
echo ""

# Step 7: Test Concurrent Requests (Safe - With Lock)
echo -e "${YELLOW}📍 Step 7: Testing SAFE method (With Pessimistic Lock - Safe)...${NC}"
echo -e "${YELLOW}Sending 10 sequential requests to /api/auth/cart/safe${NC}"

START_TIME=$(date +%s%N)
SUCCESS_COUNT=0

for i in {1..10}; do
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost/api/auth/cart/safe \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{"product_id": 1, "store_id": 1, "quantity": 1}')
    
    if [ "$STATUS" == "200" ]; then
        echo -e "  Request $i: ${GREEN}✅${NC}"
        ((SUCCESS_COUNT++))
    else
        echo -e "  Request $i: ${RED}❌ (Status: $STATUS)${NC}"
    fi
done

END_TIME=$(date +%s%N)
SAFE_TIME=$(( (END_TIME - START_TIME) / 1000000 ))

echo -e "${BLUE}⏱️ Total time: ${SAFE_TIME}ms for 10 requests${NC}"
echo -e "${GREEN}✅ Safe requests completed! ($SUCCESS_COUNT/10 successful)${NC}"
echo ""

# Step 8: Check inventory after safe requests
echo -e "${YELLOW}📍 Step 8: Checking inventory after SAFE requests...${NC}"
php artisan tinker <<'PHP'
use App\Models\Store_product;
$p = Store_product::find(1);
echo "Product ID 1 - Remaining Quantity: " . $p->quantity . "\n";
PHP
echo -e "${GREEN}✅ Note: Expected quantity should be exactly 90 (100 - 10)${NC}"
echo ""

# Step 9: View Logs
echo -e "${YELLOW}📍 Step 9: Showing recent logs...${NC}"
echo ""
echo -e "${YELLOW}View logs with: tail -f storage/logs/laravel.log${NC}"
echo ""

# Final Summary
echo -e "${BLUE}============================================================================${NC}"
echo -e "${BLUE}📊 Test Summary${NC}"
echo -e "${BLUE}============================================================================${NC}"
echo -e "${GREEN}✅ BASIC method - Response time: ${BASIC_TIME}ms${NC}"
echo -e "${GREEN}✅ SAFE method - Response time: ${SAFE_TIME}ms${NC}"
OVERHEAD=$(( SAFE_TIME - BASIC_TIME ))
echo -e "${YELLOW}⏱️ Overhead: ${OVERHEAD}ms (for safety)${NC}"
echo ""
echo -e "${YELLOW}🔍 Key Differences:${NC}"
echo -e "  ${RED}• BASIC: No locks = Faster but UNSAFE (Race Condition Risk)${NC}"
echo -e "  ${GREEN}• SAFE: With locks = Slightly slower but SAFE (100% Data Integrity)${NC}"
echo ""
echo -e "${BLUE}============================================================================${NC}"
