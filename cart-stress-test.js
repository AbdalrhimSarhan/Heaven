import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter, Trend } from 'k6/metrics';

let Status200 = new Counter('status_200');
let Status409 = new Counter('status_409');
let Status401 = new Counter('status_401');
let Status404 = new Counter('status_404');
let Status500 = new Counter('status_500');
let Status429 = new Counter('status_429');
let Status400 = new Counter('status_400');
let StatusOther = new Counter('status_other');
let ResponseTime = new Trend('response_time_ms');

// Requirement #9 - Stress Testing: 100 concurrent users
export let options = {
    stages: [
        { duration: '10s', target: 100 },  // ramp up to 100 VUs
        { duration: '30s', target: 100 },  // hold at 100 VUs for 30 seconds
        { duration: '5s',  target: 0   },  // ramp down
    ],
    thresholds: {
        // Requirement #9: system must not crash — success + expected-conflict responses only
        'status_500': ['count<5'],
        // 95% of requests must finish under 2 seconds
        'http_req_duration': ['p(95)<2000'],
    },
};

const token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcHJvZ3JhbWluZ19sYW5ndWFnZXMubG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc3NzYxMzM1LCJleHAiOjE3Nzc3NjQ5MzUsIm5iZiI6MTc3Nzc2MTMzNSwianRpIjoiY2J6cDdiS05yNkczZmpqNyIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.hfT-VK_RHtT2nnFemdPHuS1n11o4vDTZKEIFmQ_XRb8';
const headers = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` };
const payload = JSON.stringify({ product_id: 1, store_id: 1, quantity: 1 });

// Switch endpoint to compare strategies:
// ❌ Baseline (race condition):  /cart
// ✅ Req #1 (atomic DB):         /cart/integrity
// ✅ Req #7+8 (pessimistic lock): /cart/safe        ← slowest under load
// 🚀 Flash Sale (Redis):          /cart/flash        ← fastest under load
const endpoint = 'http://programing_languages.localhost/api/auth/cart/flash';
// const endpoint = 'http://programing_languages.localhost/api/auth/cart/integrity';
// const endpoint = 'http://programing_languages.localhost/api/auth/cart/safe';
// const endpoint = 'http://programing_languages.localhost/api/auth/cart';

export default function () {
    let res = http.post(endpoint, payload, { headers });

    ResponseTime.add(res.timings.duration);

    if (res.status === 200) {
        Status200.add(1);
    } else if (res.status === 409) {
        Status409.add(1);
    } else if (res.status === 401) {
        Status401.add(1);
    } else if (res.status === 404) {
        Status404.add(1);
    } else if (res.status === 500) {
        Status500.add(1);
    } else if (res.status === 429) {
        Status429.add(1);
    } else if (res.status === 400) {
        Status400.add(1);
    } else {
        StatusOther.add(1);
    }

    check(res, {
        'no server errors': (r) => r.status !== 500,
        'valid status':     (r) => [200, 409, 429].includes(r.status),
    });

    sleep(1);
}
