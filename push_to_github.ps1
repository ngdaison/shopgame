# Script đẩy code ShopGame lên GitHub
# Chạy script này trong PowerShell tại thư mục shop_game

# Bước 1: Khởi tạo Git repository
git init

# Bước 2: Thêm tất cả file (gitignore sẽ tự loại trừ file nhạy cảm)
git add .

# Bước 3: Commit lần đầu
git commit -m "Initial commit: ShopGame - Nền tảng mua bán tài khoản & vật phẩm game"

# Bước 4: Đổi branch chính thành main
git branch -M main

# Bước 5: Thêm remote repository
git remote add origin https://github.com/ngdaison/shopgame.git

# Bước 6: Push lên GitHub
git push -u origin main

Write-Host ""
Write-Host "Done! Code da duoc push len GitHub thanh cong." -ForegroundColor Green
Write-Host "Repository: https://github.com/ngdaison/shopgame" -ForegroundColor Cyan
