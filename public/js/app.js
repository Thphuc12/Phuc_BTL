/**
 * Frontend đơn giản (SPA) cho Shop API.
 * - Không dùng framework, chỉ JavaScript thuần + Bootstrap.
 * - Điều hướng bằng hash (#/products, #/cart, #/admin/...).
 * - Mọi dữ liệu đều lấy qua RESTful API: /api/...
 */

const API = '/api';

// ================== STATE (lưu trong localStorage) ==================
const state = {
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    cart: JSON.parse(localStorage.getItem('cart') || '[]'), // [{id, name, price, quantity}]
};

function saveState() {
    localStorage.setItem('token', state.token || '');
    localStorage.setItem('user', JSON.stringify(state.user));
    localStorage.setItem('cart', JSON.stringify(state.cart));
    if (!state.token) { localStorage.removeItem('token'); }
}

// ================== GỌI API ==================
/** Hàm gọi API dùng chung: tự gắn token, tự parse JSON, ném lỗi nếu success=false */
async function api(method, url, body = null) {
    const headers = { 'Accept': 'application/json' };
    if (body) headers['Content-Type'] = 'application/json';
    if (state.token) headers['Authorization'] = 'Bearer ' + state.token;

    const res = await fetch(API + url, { method, headers, body: body ? JSON.stringify(body) : null });
    const json = await res.json();

    if (res.status === 401 && state.token) { // token hết hạn -> đăng xuất
        state.token = null; state.user = null; saveState(); updateNav();
    }
    if (!json.success) {
        const err = new Error(json.message || 'Có lỗi xảy ra');
        err.errors = json.errors; err.status = res.status;
        throw err;
    }
    return json;
}

// ================== TIỆN ÍCH ==================
const money = (n) => Number(n).toLocaleString('vi-VN') + ' ₫';
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const fmtDate = (s) => new Date(s).toLocaleString('vi-VN');

const STATUS_LABEL = { pending: 'Chờ xử lý', processing: 'Đang xử lý', completed: 'Hoàn thành', cancelled: 'Đã huỷ' };
const STATUS_COLOR = { pending: 'warning', processing: 'info', completed: 'success', cancelled: 'secondary' };
const statusBadge = (s) => `<span class="badge bg-${STATUS_COLOR[s]}">${STATUS_LABEL[s]}</span>`;

function toast(message, type = 'success') {
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${type} border-0 show`;
    el.innerHTML = `<div class="d-flex"><div class="toast-body">${esc(message)}</div>
        <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.getElementById('toasts').appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

/** Hiện lỗi API: thông báo + lỗi validate (nếu có) */
function showError(e) {
    let msg = e.message;
    if (e.errors) msg += ': ' + Object.values(e.errors).flat().join(' ');
    toast(msg, 'danger');
}

function formData(form) {
    const data = {};
    new FormData(form).forEach((v, k) => { data[k] = v; });
    return data;
}

const modalEl = () => bootstrap.Modal.getOrCreateInstance(document.getElementById('modal'));
function openModal(title, bodyHtml) {
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-body').innerHTML = bodyHtml;
    modalEl().show();
}
const closeModal = () => modalEl().hide();

function pagination(meta, onPage) {
    if (meta.last_page <= 1) return '';
    let html = '<nav><ul class="pagination justify-content-center">';
    for (let i = 1; i <= meta.last_page; i++) {
        html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); ${onPage}(${i})">${i}</a></li>`;
    }
    return html + '</ul></nav>';
}

// ================== NAVBAR / AUTH ==================
function updateNav() {
    document.body.classList.toggle('logged-in', !!state.token);
    document.body.classList.toggle('is-admin', state.user?.role === 'admin');
    document.getElementById('nav-username').textContent = state.user?.name || '';
    document.getElementById('cart-count').textContent = state.cart.reduce((s, i) => s + i.quantity, 0);
}

async function logout() {
    try { await api('POST', '/auth/logout'); } catch (e) { /* bỏ qua */ }
    state.token = null; state.user = null; saveState(); updateNav();
    toast('Đã đăng xuất');
    location.hash = '#/products';
}

const requireAuth = () => { if (!state.token) { location.hash = '#/login'; return false; } return true; };
const requireAdmin = () => { if (state.user?.role !== 'admin') { toast('Bạn không có quyền', 'danger'); location.hash = '#/products'; return false; } return true; };

// ================== ROUTER ==================
const routes = {
    '/products': pageProducts,
    '/cart': pageCart,
    '/orders': pageMyOrders,
    '/profile': pageProfile,
    '/login': pageLogin,
    '/register': pageRegister,
    '/admin/dashboard': pageAdminDashboard,
    '/admin/products': pageAdminProducts,
    '/admin/categories': pageAdminCategories,
    '/admin/orders': pageAdminOrders,
    '/admin/users': pageAdminUsers,
};

function router() {
    const path = location.hash.replace('#', '') || '/products';
    const page = routes[path] || pageProducts;
    document.getElementById('app').innerHTML = '<div class="text-center py-5"><div class="spinner-border"></div></div>';
    page().catch(showError);
}
window.addEventListener('hashchange', router);
window.addEventListener('DOMContentLoaded', () => { updateNav(); router(); });

const render = (html) => { document.getElementById('app').innerHTML = html; };

// ================== TRANG: ĐĂNG NHẬP / ĐĂNG KÝ ==================
async function pageLogin() {
    render(`
    <div class="card auth-box shadow-sm"><div class="card-body p-4">
        <h4 class="mb-3 text-center">Đăng nhập</h4>
        <form onsubmit="doLogin(event)">
            <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required placeholder="Nhập email"></div>
            <div class="mb-3"><label class="form-label">Mật khẩu</label><input name="password" type="password" class="form-control" required placeholder="Nhập mật khẩu"></div>
            <button class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        <div class="text-center mt-3">Chưa có tài khoản? <a href="#/register">Đăng ký</a></div>
    </div></div>`);
}

async function doLogin(e) {
    e.preventDefault();
    try {
        const res = await api('POST', '/auth/login', formData(e.target));
        state.token = res.data.token; state.user = res.data.user; saveState(); updateNav();
        toast('Xin chào ' + state.user.name);
        location.hash = state.user.role === 'admin' ? '#/admin/dashboard' : '#/products';
    } catch (err) { showError(err); }
}

async function pageRegister() {
    render(`
    <div class="card auth-box shadow-sm"><div class="card-body p-4">
        <h4 class="mb-3 text-center">Đăng ký</h4>
        <form onsubmit="doRegister(event)">
            <div class="mb-3"><label class="form-label">Họ tên</label><input name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Số điện thoại</label><input name="phone" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Địa chỉ</label><input name="address" class="form-control"></div>
            <div class="row">
                <div class="col mb-3"><label class="form-label">Mật khẩu</label><input name="password" type="password" class="form-control" required minlength="6"></div>
                <div class="col mb-3"><label class="form-label">Nhập lại</label><input name="password_confirmation" type="password" class="form-control" required></div>
            </div>
            <button class="btn btn-success w-100">Đăng ký</button>
        </form>
        <div class="text-center mt-3">Đã có tài khoản? <a href="#/login">Đăng nhập</a></div>
    </div></div>`);
}

async function doRegister(e) {
    e.preventDefault();
    try {
        const res = await api('POST', '/auth/register', formData(e.target));
        state.token = res.data.token; state.user = res.data.user; saveState(); updateNav();
        toast('Đăng ký thành công');
        location.hash = '#/products';
    } catch (err) { showError(err); }
}

// ================== TRANG: SẢN PHẨM (public) ==================
let productFilter = { keyword: '', category_id: '', min_price: '', max_price: '', page: 1 };

async function pageProducts() {
    const cats = (await api('GET', '/categories')).data;
    render(`
    <div class="row">
        <aside class="col-lg-3 mb-3">
            <div class="card shadow-sm"><div class="card-body">
                <h6 class="fw-bold">Tìm kiếm & lọc</h6>
                <form onsubmit="applyFilter(event)">
                    <input name="keyword" class="form-control mb-2" placeholder="Tên sản phẩm..." value="${esc(productFilter.keyword)}">
                    <select name="category_id" class="form-select mb-2">
                        <option value="">-- Tất cả danh mục --</option>
                        ${cats.map(c => `<option value="${c.id}" ${productFilter.category_id == c.id ? 'selected' : ''}>${esc(c.name)} (${c.products_count})</option>`).join('')}
                    </select>
                    <div class="d-flex gap-2 mb-2">
                        <input name="min_price" type="number" class="form-control" placeholder="Giá từ" value="${productFilter.min_price}">
                        <input name="max_price" type="number" class="form-control" placeholder="Giá đến" value="${productFilter.max_price}">
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Tìm</button>
                </form>
            </div></div>
        </aside>
        <section class="col-lg-9" id="product-list"></section>
    </div>`);
    await loadProducts();
}

function applyFilter(e) {
    e.preventDefault();
    productFilter = { ...formData(e.target), page: 1 };
    loadProducts();
}

async function loadProducts(page = 1) {
    productFilter.page = page;
    const q = new URLSearchParams({ ...productFilter, status: 'active', per_page: 9 }).toString();
    const res = await api('GET', '/products?' + q);
    const { items, meta } = res.data;
    document.getElementById('product-list').innerHTML = `
        <div class="text-muted mb-2">Tìm thấy ${meta.total} sản phẩm</div>
        <div class="row g-3">
        ${items.length ? items.map(p => `
            <div class="col-md-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="product-img">${p.image ? `<img src="${esc(p.image)}">` : '<i class="bi bi-box-seam"></i>'}</div>
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted">${esc(p.category?.name)}</small>
                        <h6 class="card-title">${esc(p.name)}</h6>
                        <div class="text-danger fw-bold mb-2">${money(p.price)}</div>
                        <small class="text-muted mb-2">Còn ${p.stock} sản phẩm</small>
                        <button class="btn btn-sm btn-outline-primary mt-auto" ${p.stock === 0 ? 'disabled' : ''}
                            onclick='addToCart(${JSON.stringify({ id: p.id, name: p.name, price: p.price, stock: p.stock })})'>
                            <i class="bi bi-cart-plus"></i> ${p.stock === 0 ? 'Hết hàng' : 'Thêm vào giỏ'}
                        </button>
                    </div>
                </div>
            </div>`).join('') : '<div class="col"><div class="alert alert-light">Không có sản phẩm phù hợp.</div></div>'}
        </div>
        <div class="mt-3">${pagination(meta, 'loadProducts')}</div>`;
}

// ================== GIỎ HÀNG (localStorage) ==================
function addToCart(p) {
    const item = state.cart.find(i => i.id === p.id);
    if (item) {
        if (item.quantity >= p.stock) return toast('Vượt quá số lượng tồn kho', 'warning');
        item.quantity++;
    } else {
        state.cart.push({ id: p.id, name: p.name, price: p.price, quantity: 1 });
    }
    saveState(); updateNav();
    toast('Đã thêm "' + p.name + '" vào giỏ');
}

function changeQty(id, delta) {
    const item = state.cart.find(i => i.id === id);
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) state.cart = state.cart.filter(i => i.id !== id);
    saveState(); updateNav(); pageCart();
}

async function pageCart() {
    const total = state.cart.reduce((s, i) => s + i.price * i.quantity, 0);
    render(`
    <h4 class="mb-3"><i class="bi bi-cart3"></i> Giỏ hàng</h4>
    ${!state.cart.length ? '<div class="alert alert-light">Giỏ hàng trống. <a href="#/products">Mua sắm ngay</a></div>' : `
    <div class="row">
        <div class="col-lg-8">
            <table class="table bg-white shadow-sm">
                <thead><tr><th>Sản phẩm</th><th>Giá</th><th class="text-center">Số lượng</th><th class="text-end">Thành tiền</th></tr></thead>
                <tbody>${state.cart.map(i => `<tr>
                    <td>${esc(i.name)}</td><td>${money(i.price)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${i.id},-1)">-</button>
                        <span class="mx-2">${i.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${i.id},1)">+</button>
                    </td>
                    <td class="text-end">${money(i.price * i.quantity)}</td></tr>`).join('')}
                </tbody>
                <tfoot><tr><th colspan="3" class="text-end">Tổng cộng</th><th class="text-end text-danger">${money(total)}</th></tr></tfoot>
            </table>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm"><div class="card-body">
                <h6 class="fw-bold">Thông tin giao hàng</h6>
                ${!state.token ? '<div class="alert alert-warning small">Vui lòng <a href="#/login">đăng nhập</a> để đặt hàng.</div>' : `
                <form onsubmit="checkout(event)">
                    <div class="mb-2"><label class="form-label">Địa chỉ</label><input name="shipping_address" class="form-control" required value="${esc(state.user?.address || '')}"></div>
                    <div class="mb-2"><label class="form-label">Số điện thoại</label><input name="phone" class="form-control" required value="${esc(state.user?.phone || '')}"></div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea name="note" class="form-control" rows="2"></textarea></div>
                    <button class="btn btn-success w-100"><i class="bi bi-bag-check"></i> Đặt hàng</button>
                </form>`}
            </div></div>
        </div>
    </div>`}`);
}

async function checkout(e) {
    e.preventDefault();
    const body = { ...formData(e.target), items: state.cart.map(i => ({ product_id: i.id, quantity: i.quantity })) };
    try {
        await api('POST', '/orders', body);
        state.cart = []; saveState(); updateNav();
        toast('Đặt hàng thành công!');
        location.hash = '#/orders';
    } catch (err) { showError(err); }
}

// ================== TRANG: ĐƠN HÀNG CỦA TÔI ==================
function orderItemsHtml(o) {
    return `<ul class="mb-0 small">${o.items.map(i => `<li>${esc(i.product?.name)} × ${i.quantity} (${money(i.price)})</li>`).join('')}</ul>`;
}

async function pageMyOrders(page = 1) {
    if (!requireAuth()) return;
    const res = await api('GET', `/orders?per_page=10&page=${page}`);
    const { items, meta } = res.data;
    render(`
    <h4 class="mb-3"><i class="bi bi-receipt"></i> Đơn hàng của tôi</h4>
    ${!items.length ? '<div class="alert alert-light">Bạn chưa có đơn hàng nào.</div>' : `
    <table class="table bg-white shadow-sm">
        <thead><tr><th>#</th><th>Ngày đặt</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>${items.map(o => `<tr>
            <td>${o.id}</td><td>${fmtDate(o.created_at)}</td><td>${orderItemsHtml(o)}</td>
            <td class="fw-bold">${money(o.total_amount)}</td><td>${statusBadge(o.status)}</td>
            <td>${o.status === 'pending' ? `<button class="btn btn-sm btn-outline-danger" onclick="cancelOrder(${o.id})">Huỷ đơn</button>` : ''}</td>
        </tr>`).join('')}</tbody>
    </table>
    ${pagination(meta, 'pageMyOrders')}`}`);
}

async function cancelOrder(id) {
    if (!confirm('Huỷ đơn hàng #' + id + '?')) return;
    try { await api('PATCH', `/orders/${id}/cancel`); toast('Đã huỷ đơn'); pageMyOrders(); } catch (e) { showError(e); }
}

// ================== TRANG: THÔNG TIN CÁ NHÂN ==================
async function pageProfile() {
    if (!requireAuth()) return;
    const u = (await api('GET', '/auth/me')).data;
    render(`
    <h4 class="mb-3"><i class="bi bi-person-circle"></i> Thông tin cá nhân</h4>
    <div class="row g-3">
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <h6 class="fw-bold">Cập nhật thông tin</h6>
            <form onsubmit="updateProfile(event)">
                <div class="mb-2"><label class="form-label">Họ tên</label><input name="name" class="form-control" value="${esc(u.name)}" required></div>
                <div class="mb-2"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="${esc(u.email)}" required></div>
                <div class="mb-2"><label class="form-label">Số điện thoại</label><input name="phone" class="form-control" value="${esc(u.phone)}"></div>
                <div class="mb-3"><label class="form-label">Địa chỉ</label><input name="address" class="form-control" value="${esc(u.address)}"></div>
                <div class="mb-3"><span class="badge bg-${u.role === 'admin' ? 'danger' : 'primary'}">${u.role}</span></div>
                <button class="btn btn-primary">Lưu</button>
            </form>
        </div></div></div>
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <h6 class="fw-bold">Đổi mật khẩu</h6>
            <form onsubmit="changePassword(event)">
                <div class="mb-2"><label class="form-label">Mật khẩu hiện tại</label><input name="current_password" type="password" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">Mật khẩu mới</label><input name="new_password" type="password" class="form-control" required minlength="6"></div>
                <div class="mb-3"><label class="form-label">Nhập lại mật khẩu mới</label><input name="new_password_confirmation" type="password" class="form-control" required></div>
                <button class="btn btn-warning">Đổi mật khẩu</button>
            </form>
        </div></div></div>
    </div>`);
}

async function updateProfile(e) {
    e.preventDefault();
    try {
        const res = await api('PUT', '/auth/profile', formData(e.target));
        state.user = res.data; saveState(); updateNav();
        toast('Cập nhật thành công');
    } catch (err) { showError(err); }
}

async function changePassword(e) {
    e.preventDefault();
    try { await api('PUT', '/auth/change-password', formData(e.target)); toast('Đổi mật khẩu thành công'); e.target.reset(); } catch (err) { showError(err); }
}

// ================== ADMIN: THỐNG KÊ ==================
async function pageAdminDashboard() {
    if (!requireAuth() || !requireAdmin()) return;
    const s = (await api('GET', '/statistics')).data;
    const by = s.orders_by_status || {};
    render(`
    <h4 class="mb-3"><i class="bi bi-bar-chart"></i> Thống kê</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card stat-card shadow-sm border-primary"><div class="card-body"><div class="text-muted">Tổng đơn hàng</div><div class="stat-value">${s.total_orders}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card shadow-sm border-success"><div class="card-body"><div class="text-muted">Doanh thu (đơn hoàn thành)</div><div class="stat-value text-success">${money(s.total_revenue)}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card shadow-sm border-warning"><div class="card-body"><div class="text-muted">Chờ xử lý</div><div class="stat-value">${by.pending || 0}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card shadow-sm border-info"><div class="card-body"><div class="text-muted">Đang xử lý</div><div class="stat-value">${by.processing || 0}</div></div></div></div>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <h6 class="fw-bold">Đơn theo trạng thái</h6>
            <table class="table table-sm mb-0">${Object.keys(STATUS_LABEL).map(k => `<tr><td>${statusBadge(k)}</td><td class="text-end fw-bold">${by[k] || 0}</td></tr>`).join('')}</table>
        </div></div></div>
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <h6 class="fw-bold">Top 5 sản phẩm bán chạy</h6>
            ${s.top_products.length ? `<table class="table table-sm mb-0"><thead><tr><th>Sản phẩm</th><th class="text-end">Đã bán</th></tr></thead>
            <tbody>${s.top_products.map(t => `<tr><td>${esc(t.product?.name)}</td><td class="text-end fw-bold">${t.total_sold}</td></tr>`).join('')}</tbody></table>` : '<div class="text-muted">Chưa có dữ liệu</div>'}
        </div></div></div>
    </div>`);
}

// ================== ADMIN: SẢN PHẨM ==================
let adminCats = [];

async function pageAdminProducts(page = 1) {
    if (!requireAuth() || !requireAdmin()) return;
    adminCats = (await api('GET', '/categories')).data;
    const kw = document.getElementById('admin-kw')?.value || '';
    const res = await api('GET', `/products?per_page=10&page=${page}&keyword=${encodeURIComponent(kw)}`);
    const { items, meta } = res.data;
    render(`
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-box"></i> Quản lý sản phẩm</h4>
        <div class="d-flex gap-2">
            <input id="admin-kw" class="form-control" placeholder="Tìm tên..." value="${esc(kw)}" onkeydown="if(event.key==='Enter')pageAdminProducts()">
            <button class="btn btn-primary text-nowrap" onclick="productForm()"><i class="bi bi-plus"></i> Thêm</button>
        </div>
    </div>
    <table class="table bg-white shadow-sm">
        <thead><tr><th>#</th><th>Tên</th><th>Danh mục</th><th>Giá</th><th>Kho</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>${items.map(p => `<tr>
            <td>${p.id}</td><td>${esc(p.name)}</td><td>${esc(p.category?.name)}</td><td>${money(p.price)}</td><td>${p.stock}</td>
            <td><span class="badge bg-${p.status === 'active' ? 'success' : 'secondary'}" role="button" title="Bấm để đổi" onclick="toggleProduct(${p.id})">${p.status === 'active' ? 'Đang bán' : 'Ngừng bán'}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick='productForm(${JSON.stringify(p)})'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.id})"><i class="bi bi-trash"></i></button>
            </td></tr>`).join('')}</tbody>
    </table>
    ${pagination(meta, 'pageAdminProducts')}`);
}

function productForm(p = null) {
    openModal(p ? 'Sửa sản phẩm #' + p.id : 'Thêm sản phẩm', `
    <form onsubmit="saveProduct(event, ${p ? p.id : 'null'})">
        <div class="row">
            <div class="col-md-8 mb-2"><label class="form-label">Tên</label><input name="name" class="form-control" required value="${esc(p?.name)}"></div>
            <div class="col-md-4 mb-2"><label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select" required>${adminCats.map(c => `<option value="${c.id}" ${p?.category_id === c.id ? 'selected' : ''}>${esc(c.name)}</option>`).join('')}</select></div>
            <div class="col-md-4 mb-2"><label class="form-label">Giá</label><input name="price" type="number" min="0" class="form-control" required value="${p?.price ?? ''}"></div>
            <div class="col-md-4 mb-2"><label class="form-label">Tồn kho</label><input name="stock" type="number" min="0" class="form-control" required value="${p?.stock ?? 0}"></div>
            <div class="col-md-4 mb-2"><label class="form-label">Trạng thái</label>
                <select name="status" class="form-select"><option value="active" ${p?.status !== 'inactive' ? 'selected' : ''}>Đang bán</option><option value="inactive" ${p?.status === 'inactive' ? 'selected' : ''}>Ngừng bán</option></select></div>
            <div class="col-12 mb-2"><label class="form-label">Link ảnh</label><input name="image" class="form-control" value="${esc(p?.image)}"></div>
            <div class="col-12 mb-3"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="3">${esc(p?.description)}</textarea></div>
        </div>
        <button class="btn btn-primary">Lưu</button>
    </form>`);
}

async function saveProduct(e, id) {
    e.preventDefault();
    try {
        if (id) await api('PUT', `/products/${id}`, formData(e.target));
        else await api('POST', '/products', formData(e.target));
        closeModal(); toast('Lưu thành công'); pageAdminProducts();
    } catch (err) { showError(err); }
}

async function toggleProduct(id) {
    try { await api('PATCH', `/products/${id}/toggle-status`); pageAdminProducts(); } catch (e) { showError(e); }
}

async function deleteProduct(id) {
    if (!confirm('Xoá sản phẩm #' + id + '?')) return;
    try { await api('DELETE', `/products/${id}`); toast('Đã xoá'); pageAdminProducts(); } catch (e) { showError(e); }
}

// ================== ADMIN: DANH MỤC ==================
async function pageAdminCategories() {
    if (!requireAuth() || !requireAdmin()) return;
    const cats = (await api('GET', '/categories')).data;
    render(`
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-tags"></i> Quản lý danh mục</h4>
        <button class="btn btn-primary" onclick="categoryForm()"><i class="bi bi-plus"></i> Thêm</button>
    </div>
    <table class="table bg-white shadow-sm">
        <thead><tr><th>#</th><th>Tên</th><th>Slug</th><th>Mô tả</th><th>Số SP</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>${cats.map(c => `<tr>
            <td>${c.id}</td><td>${esc(c.name)}</td><td><code>${esc(c.slug)}</code></td><td>${esc(c.description)}</td><td>${c.products_count}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick='categoryForm(${JSON.stringify(c)})'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${c.id})"><i class="bi bi-trash"></i></button>
            </td></tr>`).join('')}</tbody>
    </table>`);
}

function categoryForm(c = null) {
    openModal(c ? 'Sửa danh mục #' + c.id : 'Thêm danh mục', `
    <form onsubmit="saveCategory(event, ${c ? c.id : 'null'})">
        <div class="mb-2"><label class="form-label">Tên</label><input name="name" class="form-control" required value="${esc(c?.name)}"></div>
        <div class="mb-3"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="2">${esc(c?.description)}</textarea></div>
        <button class="btn btn-primary">Lưu</button>
    </form>`);
}

async function saveCategory(e, id) {
    e.preventDefault();
    try {
        if (id) await api('PUT', `/categories/${id}`, formData(e.target));
        else await api('POST', '/categories', formData(e.target));
        closeModal(); toast('Lưu thành công'); pageAdminCategories();
    } catch (err) { showError(err); }
}

async function deleteCategory(id) {
    if (!confirm('Xoá danh mục #' + id + '?')) return;
    try { await api('DELETE', `/categories/${id}`); toast('Đã xoá'); pageAdminCategories(); } catch (e) { showError(e); }
}

// ================== ADMIN: ĐƠN HÀNG ==================
let adminOrderStatus = '';

async function pageAdminOrders(page = 1) {
    if (!requireAuth() || !requireAdmin()) return;
    adminOrderStatus = document.getElementById('order-status-filter')?.value ?? adminOrderStatus;
    const res = await api('GET', `/orders?per_page=10&page=${page}&status=${adminOrderStatus}`);
    const { items, meta } = res.data;
    // Các trạng thái tiếp theo được phép (giống luồng trong OrderService)
    const next = { pending: ['processing', 'cancelled'], processing: ['completed', 'cancelled'], completed: [], cancelled: [] };
    render(`
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-receipt"></i> Quản lý đơn hàng</h4>
        <select id="order-status-filter" class="form-select w-auto" onchange="pageAdminOrders()">
            <option value="">-- Tất cả trạng thái --</option>
            ${Object.entries(STATUS_LABEL).map(([k, v]) => `<option value="${k}" ${adminOrderStatus === k ? 'selected' : ''}>${v}</option>`).join('')}
        </select>
    </div>
    <table class="table bg-white shadow-sm">
        <thead><tr><th>#</th><th>Khách hàng</th><th>Sản phẩm</th><th>Giao đến</th><th>Tổng</th><th>Trạng thái</th><th>Chuyển</th></tr></thead>
        <tbody>${items.map(o => `<tr>
            <td>${o.id}<br><small class="text-muted">${fmtDate(o.created_at)}</small></td>
            <td>${esc(o.user?.name)}<br><small class="text-muted">${esc(o.user?.email)}</small></td>
            <td>${orderItemsHtml(o)}</td>
            <td><small>${esc(o.shipping_address)}<br>${esc(o.phone)}</small></td>
            <td class="fw-bold">${money(o.total_amount)}</td>
            <td>${statusBadge(o.status)}</td>
            <td>${next[o.status].map(s => `<button class="btn btn-sm btn-outline-${STATUS_COLOR[s]} me-1" onclick="setOrderStatus(${o.id}, '${s}')">${STATUS_LABEL[s]}</button>`).join('') || '<span class="text-muted">—</span>'}</td>
        </tr>`).join('')}</tbody>
    </table>
    ${pagination(meta, 'pageAdminOrders')}`);
}

async function setOrderStatus(id, status) {
    try { await api('PATCH', `/orders/${id}/status`, { status }); toast('Đã cập nhật'); pageAdminOrders(); } catch (e) { showError(e); }
}

// ================== ADMIN: NGƯỜI DÙNG ==================
async function pageAdminUsers(page = 1) {
    if (!requireAuth() || !requireAdmin()) return;
    const kw = document.getElementById('user-kw')?.value || '';
    const res = await api('GET', `/users?per_page=10&page=${page}&keyword=${encodeURIComponent(kw)}`);
    const { items, meta } = res.data;
    render(`
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-people"></i> Quản lý người dùng</h4>
        <div class="d-flex gap-2">
            <input id="user-kw" class="form-control" placeholder="Tên hoặc email..." value="${esc(kw)}" onkeydown="if(event.key==='Enter')pageAdminUsers()">
            <button class="btn btn-primary text-nowrap" onclick="userForm()"><i class="bi bi-plus"></i> Thêm</button>
        </div>
    </div>
    <table class="table bg-white shadow-sm">
        <thead><tr><th>#</th><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Vai trò</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>${items.map(u => `<tr>
            <td>${u.id}</td><td>${esc(u.name)}</td><td>${esc(u.email)}</td><td>${esc(u.phone)}</td>
            <td><span class="badge bg-${u.role === 'admin' ? 'danger' : 'primary'}">${u.role}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick='userForm(${JSON.stringify(u)})'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${u.id})" ${u.id === state.user.id ? 'disabled' : ''}><i class="bi bi-trash"></i></button>
            </td></tr>`).join('')}</tbody>
    </table>
    ${pagination(meta, 'pageAdminUsers')}`);
}

function userForm(u = null) {
    openModal(u ? 'Sửa người dùng #' + u.id : 'Thêm người dùng', `
    <form onsubmit="saveUser(event, ${u ? u.id : 'null'})">
        <div class="row">
            <div class="col-md-6 mb-2"><label class="form-label">Họ tên</label><input name="name" class="form-control" required value="${esc(u?.name)}"></div>
            <div class="col-md-6 mb-2"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required value="${esc(u?.email)}"></div>
            <div class="col-md-6 mb-2"><label class="form-label">Mật khẩu ${u ? '(bỏ trống nếu không đổi)' : ''}</label><input name="password" type="password" class="form-control" ${u ? '' : 'required'} minlength="6"></div>
            <div class="col-md-6 mb-2"><label class="form-label">Vai trò</label>
                <select name="role" class="form-select"><option value="customer" ${u?.role !== 'admin' ? 'selected' : ''}>customer</option><option value="admin" ${u?.role === 'admin' ? 'selected' : ''}>admin</option></select></div>
            <div class="col-md-6 mb-2"><label class="form-label">SĐT</label><input name="phone" class="form-control" value="${esc(u?.phone)}"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Địa chỉ</label><input name="address" class="form-control" value="${esc(u?.address)}"></div>
        </div>
        <button class="btn btn-primary">Lưu</button>
    </form>`);
}

async function saveUser(e, id) {
    e.preventDefault();
    const data = formData(e.target);
    if (!data.password) delete data.password; // không gửi password rỗng khi sửa
    try {
        if (id) await api('PUT', `/users/${id}`, data);
        else await api('POST', '/users', data);
        closeModal(); toast('Lưu thành công'); pageAdminUsers();
    } catch (err) { showError(err); }
}

async function deleteUser(id) {
    if (!confirm('Xoá người dùng #' + id + '?')) return;
    try { await api('DELETE', `/users/${id}`); toast('Đã xoá'); pageAdminUsers(); } catch (e) { showError(e); }
}
