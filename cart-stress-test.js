import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter } from 'k6/metrics'; // استيراد العداد

let Status200 = new Counter('status_200');
let Status409 = new Counter('status_409');
let Status401 = new Counter('status_401');
let Status404 = new Counter('status_404');
let Status500 = new Counter('status_500');
let StatusOther = new Counter('status_other');

export let options = {
    // vus: 20,
    // iterations: 20,
    stages: [
        { duration: '10s', target: 20 },
        { duration: '20s', target: 20 },
        { duration: '5s', target: 0 },
    ],
};

const token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcHJvZ3JhbWluZ19sYW5ndWFnZXMubG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc3NTUwODIyLCJleHAiOjE3Nzc1NTQ0MjIsIm5iZiI6MTc3NzU1MDgyMiwianRpIjoiOWpXdWkwOGxFYVpPYlQ0WSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.S7KdlhtGug8x_1Gcq8kk-s7QVjcpAHQkB2vlZ3pTo9A';
const headers = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` };
const payload = JSON.stringify({ product_id: 1, store_id: 1, quantity: 1 });
const endpoint = 'http://programing_languages.localhost/api/auth/cart/integrity';
// const endpoint = 'http://programing_languages.localhost/api/auth/cart';


export default function () {
    let res = http.post(endpoint, payload, { headers });

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
    } else {
        StatusOther.add(1);
    }

    check(res, {
        'status is valid': (r) => [200, 409, 500].includes(r.status),
    });

    sleep(1);
}