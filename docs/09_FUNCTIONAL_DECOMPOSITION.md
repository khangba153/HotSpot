# 09 — Sơ đồ phân rã chức năng Hot Spot

> Thiết kế đã chốt để nhóm tham khảo. Source hiện là bộ khung với các file xử lý rỗng; chưa triển khai MVC/Auth hoặc module nghiệp vụ.

Sơ đồ chia Hot Spot thành **5 nhóm chức năng chính**. Đường nối chỉ là quan hệ **cha–con**, không biểu thị thứ tự xử lý. Các flow theo trình tự nằm tại [Business Flow](04_BUSINESS_FLOWS.md).

```mermaid
flowchart LR
    HS[0. HOT SPOT]
    HS --- A[1. Tài khoản]
    HS --- B[2. Khám phá địa điểm]
    HS --- C[3. Đóng góp]
    HS --- D[4. Hoạt động cá nhân]
    HS --- E[5. Quản trị]
    A --- A1[1.1 Đăng ký / đăng nhập / đăng xuất]
    A --- A2[1.2 Chỉnh sửa hồ sơ]
    B --- B1[2.1 Danh sách / tìm kiếm / lọc / phân trang]
    B --- B2[2.2 Chi tiết / ảnh / nhãn / bản đồ]
    C --- C1[3.1 Đăng địa điểm 1 đến 3 ảnh]
    C --- C2[3.2 Sửa PENDING/REJECTED và gửi lại]
    C --- C3[3.3 Viết / sửa đánh giá]
    D --- D1[4.1 Địa điểm của tôi]
    D --- D2[4.2 Thêm / bỏ / xem yêu thích]
    D --- D3[4.3 Xem nơi đã bình luận]
    E --- E1[5.1 Quản lý danh mục]
    E --- E2[5.2 Duyệt / từ chối / ẩn / khôi phục Place]
    E --- E3[5.3 Ẩn / khôi phục Review]
```

| Nhóm | Thành phần chính | Người phụ trách nghiệp vụ |
|---|---|---|
| 1. Tài khoản | Đăng ký, đăng nhập, đăng xuất, hồ sơ | Khang |
| 2. Khám phá | Danh sách, tìm/lọc/phân trang, chi tiết, bản đồ | TV2 tìm kiếm; TV3 chi tiết/bản đồ |
| 3. Đóng góp | Gửi/sửa/gửi lại Place và ảnh, đánh giá | TV3 địa điểm; TV4 đánh giá |
| 4. Cá nhân | Địa điểm của tôi, yêu thích, nơi đã bình luận | TV3 địa điểm; Khang yêu thích; TV4 bình luận |
| 5. Admin | Quản lý danh mục, duyệt/từ chối/ẩn/khôi phục Place và Review | TV4 **toàn bộ** |

Không tạo nhánh thanh toán, đặt bàn, chat, bản nháp hoặc báo cáo nội dung vì các tính năng đó đã bị loại khỏi MVP.
