## Class Diagram

```mermaid
classDiagram
direction TB

class AuthController {
  +register(data)
  +login(email, password)
  +logout()
}

class PlaceController {
  +searchApproved(keyword, categoryId, district)
  +showDetail(placeId)
  +submitPlace(data, images)
}

class ReviewController {
  +saveOrUpdate(placeId, rating, comment)
}

class AdminController {
  +saveCategory(data)
  +deactivateCategory(categoryId)
  +approvePlace(placeId)
  +rejectPlace(placeId)
  +hidePlace(placeId)
  +hideReview(reviewId)
}

class User {
  +int userId
  +string fullName
  +string email
  +string passwordHash
  +UserRole role
}

class Category {
  +int categoryId
  +string name
  +bool isActive
}

class Place {
  +int placeId
  +string title
  +string description
  +string address
  +string district
  +float latitude
  +float longitude
  +PlaceStatus status
}

class PlaceImage {
  +int imageId
  +string imageUrl
  +int sortOrder
}

class Review {
  +int reviewId
  +int rating
  +string comment
  +ReviewStatus status
  +datetime createdAt
}

class UserRole {
  <<enumeration>>
  MEMBER
  ADMIN
}

class PlaceStatus {
  <<enumeration>>
  PENDING
  APPROVED
  REJECTED
  HIDDEN
}

class ReviewStatus {
  <<enumeration>>
  VISIBLE
  HIDDEN
}

class NominatimAPI {
  <<external API>>
  +searchAddress(address)
}

User "1" --> "0..*" Place : creates
Category "1" --> "0..*" Place : classifies
Place "1" *-- "1..3" PlaceImage : contains
User "1" --> "0..*" Review : writes
Place "1" --> "0..*" Review : receives

User --> UserRole : role
Place --> PlaceStatus : status
Review --> ReviewStatus : status

AuthController ..> User : uses
PlaceController ..> Place : uses
PlaceController ..> PlaceImage : uses
PlaceController ..> Category : uses
PlaceController ..> NominatimAPI : geocodes address
ReviewController ..> Review : uses
AdminController ..> Category : manages
AdminController ..> Place : moderates
AdminController ..> Review : moderates
```

> `latitude` và `longitude` có thể để trống nếu không tích hợp API tọa độ. Nếu dự án chưa dùng Nominatim, xóa lớp `NominatimAPI` và quan hệ tương ứng. Cơ sở dữ liệu nên đặt ràng buộc `UNIQUE(userId, placeId)` để mỗi thành viên chỉ có một đánh giá cho một địa điểm.

## Business Flow

```mermaid
flowchart TD
  A([Bắt đầu]) --> B{Chọn chức năng}

  B --> C[Tra cứu địa điểm]
  C --> C1[Chỉ lấy địa điểm APPROVED]
  C1 --> C2[Hiển thị danh sách và chi tiết]

  B --> D[Đăng địa điểm]
  D --> E{Đã đăng nhập?}
  E -- Chưa --> F[Đăng nhập hoặc đăng ký]
  E -- Rồi --> G[Nhập thông tin và tối đa 3 ảnh]
  F --> G
  G --> H{Thông tin hợp lệ?}
  H -- Chưa --> G
  H -- Rồi --> I[Lưu Place PENDING và PlaceImage]
  I --> J[Admin xem xét]
  J --> K{Kết quả duyệt?}
  K -- Duyệt --> L[Chuyển thành APPROVED và hiển thị]
  K -- Từ chối --> M[Chuyển thành REJECTED và không hiển thị]

  B --> N[Viết hoặc cập nhật đánh giá]
  N --> O{Đã đăng nhập?}
  O -- Chưa --> P[Đăng nhập hoặc đăng ký]
  O -- Rồi --> Q[Lưu hoặc cập nhật Review]
  P --> Q
  Q --> R[Đặt trạng thái VISIBLE và hiển thị]
  R --> S{Admin cần ẩn đánh giá?}
  S -- Có --> T[Chuyển trạng thái thành HIDDEN]
  S -- Không --> U[Giữ đánh giá VISIBLE]

  B --> V[Admin quản lý danh mục]
```
