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

const tokens = [
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2Mzg5LCJleHAiOjE4MTA3NDIzODksIm5iZiI6MTc3OTIwNjM4OSwianRpIjoiaWdabUJUOXhBeThRYjVMUyIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.N9TwzovgRrz2sUpDHMVzBfoV5auLKmu_RkThcYJ-FZg',   // mobile: 0100123456
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2Mzg5LCJleHAiOjE4MTA3NDIzODksIm5iZiI6MTc3OTIwNjM4OSwianRpIjoiUlZFRDRNVmRtT2ZBZXY3TSIsInN1YiI6IjMiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.1qYSE9WwYnvacYzmnYDrPtFgrgrXmcRueIzqbUXuHas',   // mobile: 0100234567
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2MzkwLCJleHAiOjE4MTA3NDIzOTAsIm5iZiI6MTc3OTIwNjM5MCwianRpIjoiYkdCaWk4M1I5NGhNSnV0WiIsInN1YiI6IjQiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.Bd3jGusqgCe--kfUAVqgcfn6odwV8DYSGFxk0u7QapQ',   // mobile: 0100345678
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2MzkwLCJleHAiOjE4MTA3NDIzOTAsIm5iZiI6MTc3OTIwNjM5MCwianRpIjoiZWtGTmJCRVJKR21FMm9ndSIsInN1YiI6IjI1IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.J5hSlCw7XHN1w8_CL284dlBSzq-BQs1C9WeyyU-z01A',   // mobile: 0900000005
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2MzkwLCJleHAiOjE4MTA3NDIzOTAsIm5iZiI6MTc3OTIwNjM5MCwianRpIjoidTR1dDBreGlhMk9YeThCNiIsInN1YiI6IjI2IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.wN3xc61bwCrrJln7O9WyiRd2VSRkAsaNLBCRdMTsmzo',   // mobile: 0900000006
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2MzkwLCJleHAiOjE4MTA3NDIzOTAsIm5iZiI6MTc3OTIwNjM5MCwianRpIjoibU9POFE3VWM4RVZiVUpMcSIsInN1YiI6IjI3IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.3dM1jDXbFMMMWQjuMjFV51F50x9W1bWv45jiDlrovLc',   // mobile: 0900000007
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2MzkwLCJleHAiOjE4MTA3NDIzOTAsIm5iZiI6MTc3OTIwNjM5MCwianRpIjoiNzY5ZmtqTFBqNlJjcW9CQyIsInN1YiI6IjI4IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.ml-ku2sfwWSE8CydPtI6AcyGDjLIG7xu2SQLbGQJC5A',   // mobile: 0900000008
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2MzkwLCJleHAiOjE4MTA3NDIzOTAsIm5iZiI6MTc3OTIwNjM5MCwianRpIjoiWXo1VmtXWVI2bVNiN2FhNyIsInN1YiI6IjI5IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.j1m6qrLVVFmcK5Dgi5-wfzMLfnSxDWh6Rlmgwc0-gIU',   // mobile: 0900000009
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5MjA2NDE2LCJleHAiOjE4MTA3NDI0MTYsIm5iZiI6MTc3OTIwNjQxNiwianRpIjoiMWpXZmhYVkRzc0tXdXNzNiIsInN1YiI6IjMxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.ZmPrYFRP8BaoTk7YUNchZ4-rdYaexNlmPbbFhKY_V7g',   // mobile: 0900000011
];

const payload = JSON.stringify({ product_id: 1, store_id: 1, quantity: 1 });

// Switch endpoint via env: k6 run -e ENDPOINT=flash cart-stress-test.js
// Strategies: baseline | integrity | safe | flash
const strategies = {
    baseline:  'http://localhost/api/auth/cart',
    integrity: 'http://localhost/api/auth/cart/integrity',
    safe:      'http://localhost/api/auth/cart/safe',
    flash:     'http://localhost/api/auth/cart/flash',
};
const endpoint = strategies[__ENV.ENDPOINT] || strategies.flash;

export default function () {
    const token = tokens[(__VU - 1) % tokens.length];
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

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
