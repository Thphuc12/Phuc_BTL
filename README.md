# Website Bán Hàng - RESTful API (Laravel)

Bài tập lớn: Xây dựng hệ thống Website theo kiến trúc RESTful API với Laravel,
tổ chức theo mô hình phân tầng **Controller → Service → Repository → Model**.

## 1. Công nghệ

| Thành phần | Công nghệ |
|---|---|
| Ngôn ngữ | PHP 8.3 |
| Framework | Laravel 13 |
| Database | SQLite (mặc định, chạy ngay) hoặc MySQL (đổi trong `.env`) |
| Xác thực | Laravel Sanctum (Bearer Token) |
| Phân quyền | Middleware `role:admin` |
| Test API | Postman (file `postman_collection.json`) |

## 2. Cài đặt và chạy (Windows)

### 2.1. Cài công cụ (chỉ làm một lần trên mỗi máy)

1. **PHP 8.3**: mở PowerShell, chạy `winget install PHP.PHP.8.3`. Đóng và mở lại PowerShell, kiểm tra `php -v`.
2. **Bật extension cho PHP** (bản winget chưa có `php.ini`). Dán khối sau vào PowerShell:

```powershell
$phpDir = Split-Path (Get-Command php).Source
Copy-Item "$phpDir\php.ini-development" "$phpDir\php.ini"
$ini = Get-Content "$phpDir\php.ini"
$ini = $ini -replace '^;?extension_dir = "ext"', 'extension_dir = "ext"'
foreach ($ext in 'curl','fileinfo','mbstring','openssl','pdo_mysql','pdo_sqlite','sqlite3','zip','intl','gd') {
    $ini = $ini -replace "^;extension=$ext$", "extension=$ext"
}
$ini | Set-Content "$phpDir\php.ini" -Encoding utf8
```

3. **Composer**: tải `Composer-Setup.exe` tại https://getcomposer.org/download và cài mặc định. Mở lại PowerShell, kiểm tra `composer -V`.
4. **Git**: tải tại https://git-scm.com/download/win nếu chưa có.

Nếu đã dùng XAMPP hoặc Laragon thì PHP, MySQL đã có sẵn, chỉ cần cài Composer và Git.

### 2.2. Chạy dự án

> Lưu ý: trong PowerShell gõ **từng lệnh một**, chờ xong mới gõ lệnh tiếp.
> Nếu thấy dấu `>>` nghĩa là bạn đã dán nhiều dòng, nhấn `Ctrl + C` và làm lại.
> Không chạy trong `C:\WINDOWS\system32`, hãy `cd` sang ổ D hoặc Documents trước.

```powershell
cd D:\
git clone https://github.com/Thphuc12/Phuc_BTL.git
cd Phuc_BTL
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Mở trình duyệt: giao diện tại `http://127.0.0.1:8000`, API tại `http://127.0.0.1:8000/api`.
Giữ cửa sổ PowerShell mở trong lúc dùng, nhấn `Ctrl + C` để dừng server.

**Lần sau chạy lại** chỉ cần:

```powershell
cd D:\Phuc_BTL
php artisan serve
```

**Reset dữ liệu về ban đầu:** `php artisan migrate:fresh --seed`

### 2.3. Dùng MySQL thay SQLite (khi cần phpMyAdmin để báo cáo)

Bật MySQL trong XAMPP, import file `database/shop_api.sql` bằng phpMyAdmin (tab Import) hoặc:

```bash
mysql -u root -p < database/shop_api.sql
```

Mở `.env`, sửa:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shop_api
DB_USERNAME=root
DB_PASSWORD=
```

Rồi `php artisan serve`. File SQL đã có sẵn bảng và dữ liệu mẫu nên không cần migrate nữa.

### 2.4. Tài khoản mẫu

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | admin@example.com | 123456 |
| Customer | customer@example.com | 123456 |

### 2.5. Lỗi hay gặp

| Lỗi | Nguyên nhân và cách sửa |
|---|---|
| `No application encryption key has been specified` | Chưa chạy `php artisan key:generate` |
| `Could not open input file: artisan` | Đang đứng sai thư mục, `cd` vào thư mục dự án |
| `Class "Laravel\Sanctum\..." not found` | Chưa chạy `composer install` hoặc chạy chưa xong |
| `The file database.sqlite already exists` | Bình thường, bỏ qua |
| Thiếu extension `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo` | Chưa bật trong `php.ini`, xem mục 2.1 bước 2 |
| `could not find driver` khi dùng MySQL | Chưa bật `extension=pdo_mysql` trong `php.ini` |

## 3. Kiến trúc phân tầng

```
Client → routes/api.php → Controller → Service → Repository → Model → Database
```

| Tầng | Thư mục | Vai trò |
|---|---|---|
| Route | `routes/api.php` | Định nghĩa endpoint, gắn middleware |
| Request | `app/Http/Requests/` | Validate dữ liệu đầu vào, trả 422 nếu sai |
| Middleware | `app/Http/Middleware/RoleMiddleware.php` | Kiểm tra role (admin / customer) |
| Controller | `app/Http/Controllers/API/` | Nhận request, gọi Service, trả JSON. **Không chứa nghiệp vụ** |
| Service | `app/Services/` | Toàn bộ business logic (kiểm tra tồn kho, luồng trạng thái đơn, ...) |
| Interface | `app/Interfaces/` | Hợp đồng cho Repository |
| Repository | `app/Repositories/` | Truy vấn database (Eloquent) |
| Model | `app/Models/` | Mapping bảng dữ liệu, quan hệ |
| Helper | `app/Helpers/ApiResponse.php` | Chuẩn hoá JSON response |
| Exception | `app/Exceptions/BusinessException.php` | Lỗi nghiệp vụ, tự trả JSON với mã HTTP phù hợp |
| Provider | `app/Providers/RepositoryServiceProvider.php` | Bind Interface → Repository |

### Ví dụ luồng "Đặt hàng"

1. `POST /api/orders` → `StoreOrderRequest` validate giỏ hàng
2. `OrderController@store` gọi `OrderService::create()`
3. `OrderService` kiểm tra sản phẩm còn bán, đủ kho → tính tổng tiền → trừ kho (trong transaction)
4. `OrderRepository` lưu `orders` + `order_items`
5. Controller trả `201 Created` với JSON đơn hàng

## 4. Cấu trúc JSON response

Mọi API trả cùng một cấu trúc:

```json
{ "success": true,  "message": "Thành công", "data": { ... } }
{ "success": false, "message": "Dữ liệu không hợp lệ", "errors": { "email": ["..."] } }
```

Danh sách có phân trang:

```json
{ "success": true, "data": { "items": [...], "meta": { "current_page": 1, "per_page": 10, "total": 25, "last_page": 3 } } }
```

| Mã HTTP | Ý nghĩa |
|---|---|
| 200 | Thành công |
| 201 | Tạo mới thành công |
| 400 | Lỗi nghiệp vụ (hết hàng, sai luồng trạng thái, ...) |
| 401 | Chưa đăng nhập / token sai |
| 403 | Không đủ quyền |
| 404 | Không tìm thấy |
| 422 | Dữ liệu đầu vào không hợp lệ |

## 5. Danh sách API

Header cho các API cần đăng nhập: `Authorization: Bearer <token>` và `Accept: application/json`.

### 5.1 Xác thực và tài khoản cá nhân

| Method | Endpoint | Quyền | Mô tả |
|---|---|---|---|
| POST | /api/auth/register | Public | Đăng ký (name, email, password, password_confirmation) |
| POST | /api/auth/login | Public | Đăng nhập, trả token |
| POST | /api/auth/logout | Đã đăng nhập | Đăng xuất (xoá token) |
| GET | /api/auth/me | Đã đăng nhập | Thông tin cá nhân |
| PUT | /api/auth/profile | Đã đăng nhập | Cập nhật name, email, phone, address |
| PUT | /api/auth/change-password | Đã đăng nhập | Đổi mật khẩu (current_password, new_password, new_password_confirmation) |

### 5.2 Quản lý người dùng (Admin)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | /api/users?keyword=&per_page= | Danh sách, tìm theo tên/email, phân trang |
| GET | /api/users/{id} | Chi tiết |
| POST | /api/users | Tạo user (có chọn role) |
| PUT | /api/users/{id} | Cập nhật |
| DELETE | /api/users/{id} | Xoá (không xoá được chính mình) |

### 5.3 Quản lý danh mục

| Method | Endpoint | Quyền | Mô tả |
|---|---|---|---|
| GET | /api/categories | Public | Danh sách kèm số sản phẩm |
| GET | /api/categories/{id} | Public | Chi tiết |
| POST | /api/categories | Admin | Tạo (slug tự sinh) |
| PUT | /api/categories/{id} | Admin | Cập nhật |
| DELETE | /api/categories/{id} | Admin | Xoá (chặn nếu còn sản phẩm) |

### 5.4 Quản lý sản phẩm

| Method | Endpoint | Quyền | Mô tả |
|---|---|---|---|
| GET | /api/products | Public | Tìm kiếm + lọc + phân trang: `keyword, category_id, status, min_price, max_price, per_page, page` |
| GET | /api/products/{id} | Public | Chi tiết |
| POST | /api/products | Admin | Tạo |
| PUT | /api/products/{id} | Admin | Cập nhật |
| DELETE | /api/products/{id} | Admin | Xoá |
| PATCH | /api/products/{id}/toggle-status | Admin | Bật/tắt trạng thái bán |

### 5.5 Quản lý đơn hàng và thống kê

| Method | Endpoint | Quyền | Mô tả |
|---|---|---|---|
| GET | /api/orders?status=&per_page= | Đã đăng nhập | Admin xem tất cả, customer chỉ xem đơn mình |
| GET | /api/orders/{id} | Đã đăng nhập | Chi tiết (customer chỉ xem đơn mình) |
| POST | /api/orders | Đã đăng nhập | Đặt hàng, tự trừ tồn kho |
| PATCH | /api/orders/{id}/cancel | Đã đăng nhập | Customer huỷ đơn khi còn `pending`, hoàn kho |
| PATCH | /api/orders/{id}/status | Admin | Đổi trạng thái theo luồng |
| GET | /api/statistics | Admin | Tổng đơn, đơn theo trạng thái, doanh thu, top 5 sản phẩm |

Luồng trạng thái đơn hàng:

```
pending → processing → completed
   ↓           ↓
cancelled   cancelled
```

Ví dụ body đặt hàng:

```json
{
  "shipping_address": "123 Lê Lợi, Quận 1",
  "phone": "0912345678",
  "note": "Giao giờ hành chính",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 6, "quantity": 1 }
  ]
}
```

## 6. Gợi ý phân công nhóm (mỗi người tối thiểu 2 chức năng)

| Thành viên | Chức năng | File chính |
|---|---|---|
| Người 1 | Đăng ký / Đăng nhập / Thông tin cá nhân + Phân quyền | `AuthController`, `AuthService`, `RoleMiddleware`, `Requests/Auth/*` |
| Người 2 | Quản lý người dùng + Quản lý danh mục | `UserController`, `UserService`, `CategoryController`, `CategoryService` |
| Người 3 | Quản lý sản phẩm (CRUD, tìm kiếm, lọc, phân trang) + Quản lý đơn hàng (đặt hàng, trạng thái, thống kê) | `ProductController`, `ProductService`, `OrderController`, `OrderService` |

## 7. Bảo mật

- **Authentication**: Sanctum token, route nhóm `auth:sanctum`.
- **Authorization**: middleware `role:admin`; customer chỉ xem/huỷ đơn của mình (kiểm tra trong `OrderService`).
- **Validation**: mọi input đi qua Form Request, trả 422.
- **Chống truy cập trái phép**: không token → 401, sai role → 403, role không thể tự set khi đăng ký.
- Mật khẩu được hash tự động (cast `hashed` trong `User` model).

## 8. Giao diện demo (Frontend)

Mở `http://127.0.0.1:8000/` sau khi chạy `php artisan serve`.

Frontend là một trang đơn (SPA) viết bằng **HTML + JavaScript thuần + Bootstrap 5**, không cần cài Node.
Toàn bộ dữ liệu đều lấy qua RESTful API ở trên bằng `fetch`, token lưu trong `localStorage`.

| File | Vai trò |
|---|---|
| `resources/views/app.blade.php` | Khung HTML (navbar, modal, vùng nội dung) |
| `public/js/app.js` | Gọi API, điều hướng theo hash (`#/products`, `#/cart`, `#/admin/...`), render từng trang |
| `public/css/app.css` | Style bổ sung |

Chức năng trên giao diện:
- **Khách / Customer**: xem sản phẩm (tìm kiếm, lọc danh mục, lọc giá, phân trang), giỏ hàng, đặt hàng, xem và huỷ đơn, sửa thông tin cá nhân, đổi mật khẩu, đăng ký, đăng nhập.
- **Admin**: thống kê, quản lý sản phẩm, danh mục, đơn hàng (chuyển trạng thái), người dùng.

## 9. Test bằng Postman

Import file `postman_collection.json`. Collection có biến `base_url`, `admin_token`, `customer_token`.
Gọi **Login Admin** / **Login Customer** trước, token sẽ tự lưu vào biến nhờ script test.
