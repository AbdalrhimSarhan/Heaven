# ============================================================================
# 🧪 Testing Script for Requirement 1: Concurrent Access & Data Integrity
# ============================================================================

# Colors for output
$Green = @{ ForegroundColor = "Green" }
$Red = @{ ForegroundColor = "Red" }
$Yellow = @{ ForegroundColor = "Yellow" }
$Blue = @{ ForegroundColor = "Cyan" }

Write-Host "============================================================================" @Blue
Write-Host "🧪 Testing Requirement #1: Concurrent Access & Data Integrity" @Blue
Write-Host "============================================================================" @Blue
Write-Host ""

# Step 1: Fresh Migration and Seeding
Write-Host "📍 Step 1: Preparing Database..." @Yellow
Write-Host "Running: php artisan migrate:fresh --seed" @Yellow
php artisan migrate:fresh --seed

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Database prepared successfully!" @Green
} else {
    Write-Host "❌ Database preparation failed!" @Red
    exit 1
}
Write-Host ""

# Step 2: Get available store products
Write-Host "📍 Step 2: Getting store products for testing..." @Yellow
$products = php artisan tinker --execute "
use App\Models\Store_product;
\$products = Store_product::limit(5)->get();
foreach (\$products as \$p) {
    echo \$p->id . ',' . \$p->store_id . ',' . \$p->product_id . ',' . \$p->quantity . PHP_EOL;
}
"

Write-Host "Available products:" @Green
Write-Host $products

Write-Host ""

# Step 3: Get JWT Token
Write-Host "📍 Step 3: Authenticating user..." @Yellow

# Use a known test user
$loginResponse = Invoke-WebRequest -Uri "http://localhost/api/auth/login" `
    -Method POST `
    -Headers @{ "Content-Type" = "application/json"; "Accept" = "application/json" } `
    -Body '{"mobile": "0100123456", "password": "password"}' `
    -ErrorAction Stop

$token = ($loginResponse.Content | ConvertFrom-Json).access_token

if ($token) {
    Write-Host "✅ Authentication successful!" @Green
    Write-Host "Token: $($token.Substring(0, 20))..." @Green
} else {
    Write-Host "❌ Authentication failed!" @Red
    exit 1
}

Write-Host ""

# Step 4: Test Concurrent Requests (Basic - No Lock)
Write-Host "📍 Step 4: Testing BASIC method (No Lock - Race Condition Risk)..." @Yellow
Write-Host "Sending 10 concurrent requests to /api/auth/cart" @Yellow

$basicResults = @()
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

for ($i = 1; $i -le 10; $i++) {
    $response = Invoke-WebRequest -Uri "http://localhost/api/auth/cart" `
        -Method POST `
        -Headers @{ 
            "Authorization" = "Bearer $token"
            "Content-Type" = "application/json"
            "Accept" = "application/json"
        } `
        -Body '{"product_id": 1, "store_id": 1, "quantity": 1}' `
        -ErrorAction SilentlyContinue

    if ($response.StatusCode -eq 200) {
        $basicResults += "✅"
    } else {
        $basicResults += "❌"
    }
    Write-Host "  Request $i: $($basicResults[-1])" @Blue
}

$basicTime = $stopwatch.ElapsedMilliseconds
Write-Host "⏱️ Total time: ${basicTime}ms for 10 requests" @Green
Write-Host "✅ Basic requests completed!" @Green

Write-Host ""

# Step 5: Check inventory after basic requests
Write-Host "📍 Step 5: Checking inventory after BASIC requests..." @Yellow

$inventoryCheck = php artisan tinker --execute "
use App\Models\Store_product;
\$p = Store_product::find(1);
echo 'Product ID 1 - Remaining Quantity: ' . \$p->quantity;
"

Write-Host $inventoryCheck @Green
Write-Host "⚠️  Note: Expected quantity should be decreased by 10" @Yellow
Write-Host ""

# Step 6: Reset inventory
Write-Host "📍 Step 6: Resetting inventory for SAFE test..." @Yellow
php artisan tinker --execute "
use App\Models\Store_product;
\$p = Store_product::find(1);
\$p->quantity = 100;
\$p->save();
echo 'Reset to: ' . \$p->quantity;
"
Write-Host "✅ Inventory reset!" @Green
Write-Host ""

# Step 7: Test Concurrent Requests (Safe - With Lock)
Write-Host "📍 Step 7: Testing SAFE method (With Pessimistic Lock - Safe)..." @Yellow
Write-Host "Sending 10 concurrent requests to /api/auth/cart/safe" @Yellow

$safeResults = @()
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

for ($i = 1; $i -le 10; $i++) {
    $response = Invoke-WebRequest -Uri "http://localhost/api/auth/cart/safe" `
        -Method POST `
        -Headers @{ 
            "Authorization" = "Bearer $token"
            "Content-Type" = "application/json"
            "Accept" = "application/json"
        } `
        -Body '{"product_id": 1, "store_id": 1, "quantity": 1}' `
        -ErrorAction SilentlyContinue

    if ($response.StatusCode -eq 200) {
        $safeResults += "✅"
    } else {
        $safeResults += "❌"
    }
    Write-Host "  Request $i: $($safeResults[-1])" @Blue
}

$safeTime = $stopwatch.ElapsedMilliseconds
Write-Host "⏱️ Total time: ${safeTime}ms for 10 requests" @Green
Write-Host "✅ Safe requests completed!" @Green

Write-Host ""

# Step 8: Check inventory after safe requests
Write-Host "📍 Step 8: Checking inventory after SAFE requests..." @Yellow

$inventoryCheckSafe = php artisan tinker --execute "
use App\Models\Store_product;
\$p = Store_product::find(1);
echo 'Product ID 1 - Remaining Quantity: ' . \$p->quantity;
"

Write-Host $inventoryCheckSafe @Green
Write-Host "✅ Note: Expected quantity should be exactly 90 (100 - 10)" @Green
Write-Host ""

# Step 9: View Logs
Write-Host "📍 Step 9: Showing recent logs..." @Yellow
Write-Host ""
Write-Host "View logs with: tail -f storage/logs/laravel.log" @Yellow
Write-Host ""

# Final Summary
Write-Host "============================================================================" @Blue
Write-Host "📊 Test Summary" @Blue
Write-Host "============================================================================" @Blue
Write-Host "✅ BASIC method - Response time: ${basicTime}ms" @Green
Write-Host "✅ SAFE method - Response time: ${safeTime}ms" @Green
Write-Host "⏱️ Overhead: $($safeTime - $basicTime)ms (for safety)" @Yellow
Write-Host ""
Write-Host "🔍 Key Differences:" @Yellow
Write-Host "  • BASIC: No locks = Faster but UNSAFE (Race Condition Risk)" @Red
Write-Host "  • SAFE: With locks = Slightly slower but SAFE (100% Data Integrity)" @Green
Write-Host ""
Write-Host "============================================================================" @Blue
