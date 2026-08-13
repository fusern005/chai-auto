# 🚀 คู่มือการนำโปรเจกต์ Suchai Auto ขึ้น Render.com (dashboard.render.com)

ระบบได้รับการจัดเตรียมไฟล์ **`Dockerfile`**, **`render.yaml`** และรองรับ **HTTPS** และ **Google Sheets Environment Variable** เพื่อความง่ายในการนำขึ้น Render.com เรียบร้อยแล้วครับ

---

## 📌 ขั้นตอนการเอาขึ้น Render.com (แบบละเอียด)

### ขั้นตอนที่ 1: Push โค้ดขึ้น GitHub / GitLab
1. อัปโหลดโปรเจกต์นี้ขึ้น GitHub Repository ของคุณ (มั่นใจได้เลยว่าไฟล์ความลับ `.env` และ `API/*.json` จะไม่ติดขึ้นไปเนื่องจาก `.gitignore` บล็อกไว้เรียบร้อยแล้ว)

---

### ขั้นตอนที่ 2: สร้าง Web Service บน Render.com
1. เข้าไปที่ [dashboard.render.com](https://dashboard.render.com/) แล้วเข้าสู่ระบบ
2. กดปุ่ม **New +** (มุมขวาบน) -> เลือก **Web Service**
3. เชื่อมต่อบัญชี GitHub และเลือก Repository โปรเจกต์ **Suchai_auto** ของคุณ
4. ตั้งค่าหน้าเว็บดังนี้:
   - **Name:** `suchai-auto` (หรือชื่อตามต้องการ)
   - **Language / Environment:** เลือก **Docker**
   - **Region:** เลือก **Singapore** (หรือตำแหน่งใกล้ประเทศไทยที่สุด)
   - **Branch:** `main` (หรือ `master`)
   - **Instance Type:** เลือก **Free**

---

### ขั้นตอนที่ 3: ตั้งค่า Environment Variables (สิ่งสำคัญมาก)
เลื่อนลงมาที่หัวข้อ **Environment Variables** กด **Add Environment Variable** และใส่ค่าดังนี้:

| Key | Value / คำแนะนำ |
|---|---|
| **`APP_ENV`** | `production` |
| **`APP_DEBUG`** | `false` |
| **`APP_KEY`** | กด Generate หรือใช้ค่า `base64:U4p05OGu8iyiNsCf9RDY9kKSnDRhoVm6leii3ox56Mw=` |
| **`APP_URL`** | ใส่ URL ของ Render (เช่น `https://suchai-auto.onrender.com`) |
| **`SESSION_DRIVER`** | `cookie` |
| **`GOOGLE_SPREADSHEET_ID`** | `171-5miq9QCluZWh1fvLbpgIUKKGp2D2RLJomKS5jTh8` (หรือ ID ชีตของคุณ) |
| **`GOOGLE_SERVICE_ACCOUNT_JSON`** | **เปิดไฟล์ `.json` ของ Google Service Account ในเครื่องของคุณทั้งหมด แล้วคัดลอกข้อความ JSON ทั้งหมดมาวางใส่ในช่องนี้ตรงๆ ได้เลยครับ** |

> 💡 **ข้อดี:** การวาง JSON ใน `GOOGLE_SERVICE_ACCOUNT_JSON` ทำให้ไม่ต้องอัปโหลดไฟล์ Credentials ขึ้น Git ปลอดภัย 100%!

---

### ขั้นตอนที่ 4: เริ่มการ Deploy
1. กดปุ่ม **Create Web Service**
2. Render จะทำการ Build Docker Container อัตโนมัติ (ใช้เวลาประมาณ 2-3 นาที)
3. เมื่อขึ้นสถานะ **Live** คุณสามารถกดเปิดลิงก์ `https://suchai-auto.onrender.com` เพื่อใช้งานได้ทันทีครับ! 🎉
