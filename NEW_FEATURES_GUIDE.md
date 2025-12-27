# MicroBoard 새로운 기능 추가 완료 ✅

## 📋 추가된 핵심 기능

### 1. 🔑 비밀번호 재설정 시스템
**파일:**
- `password_reset.php` - 비밀번호 재설정 요청 페이지
- `password_reset_confirm.php` - 토큰 검증 및 새 비밀번호 설정 페이지

**기능:**
- 이메일을 통한 비밀번호 재설정 링크 발송
- 1시간 유효 토큰 시스템
- 안전한 비밀번호 변경 프로세스
- 로그인 페이지에 "비밀번호를 잊으셨나요?" 링크 추가

**사용 방법:**
1. 로그인 페이지에서 "🔑 비밀번호를 잊으셨나요?" 클릭
2. 등록된 이메일 주소 입력
3. 이메일로 받은 링크 클릭
4. 새 비밀번호 설정

---

### 2. 👍 게시글 추천/좋아요 시스템
**파일:**
- `update_db_likes.php` - 데이터베이스 설치 스크립트
- `like_post.php` - AJAX API (추천/취소/확인)

**기능:**
- 게시글별 추천 수 표시
- 중복 추천 방지
- 실시간 추천 수 업데이트
- 추천 취소 기능

**설치:**
```
http://your-domain/update_db_likes.php
```

**사용 방법:**
- 게시글 보기 페이지에서 "👍 추천" 버튼 클릭
- AJAX로 즉시 반영
- 다시 클릭하면 추천 취소

---

### 3. 🚨 신고 시스템
**파일:**
- `update_db_reports.php` - 데이터베이스 설치 스크립트
- `report.php` - 신고 접수 API
- `admin/reports.php` - 관리자 신고 관리 페이지

**기능:**
- 게시글/댓글 신고 기능
- 신고 사유 선택 및 상세 설명
- 중복 신고 방지
- 관리자 검토 및 처리 워크플로우
- 신고 통계 대시보드

**설치:**
```
http://your-domain/update_db_reports.php
```

**관리자 기능:**
- 신고 목록 조회 (대기/처리완료/기각)
- 신고 내용 확인 및 원본 게시글 바로가기
- 신고 처리/기각 및 관리자 메모
- 신고 통계 확인

---

### 4. 📊 방문자 통계
**파일:**
- `inc/visit_tracker.php` - 방문자 추적 스크립트
- `admin/visit_stats.php` - 관리자 통계 페이지

**기능:**
- 일별 방문자 수 추적
- 순 방문자 (Unique Visitors) 집계
- 페이지뷰 카운팅
- 기간별 통계 (7일/30일/90일/1년)
- 시각화된 차트 및 상세 테이블

**설치:**
방문자 추적을 활성화하려면 `inc/header.php`에 다음 코드 추가:
```php
<?php require_once __DIR__ . '/../inc/visit_tracker.php'; ?>
```

**관리자 페이지:**
- 오늘의 통계 (실시간)
- 기간별 총계 및 평균
- 일별 방문자 추이 차트
- 상세 통계 테이블

---

### 5. 🚫 IP 차단 시스템
**파일:**
- `admin/ip_ban.php` - IP 차단 관리 페이지

**기능:**
- IP 주소별 접근 차단
- 차단 사유 기록
- 만료 날짜 설정 (선택사항)
- 차단 해제 기능

**사용 방법:**
1. 관리자 페이지 → IP Ban Management
2. IP 주소, 사유, 만료일 입력
3. "Ban IP" 클릭

---

### 6. 📢 공지사항 시스템
**파일:**
- `admin/notice.php` - 공지사항 관리 페이지

**기능:**
- 공지사항 생성/수정/삭제
- 활성화/비활성화 토글
- 만료 날짜 설정
- 메인 페이지 표시

---

### 7. 📥 다운로드 카운터
**수정된 파일:**
- `download.php` - 다운로드 카운터 증가 로직 활성화
- `config.php` - `incrementDownload()` 함수 추가

**기능:**
- 파일 다운로드 시 자동 카운터 증가
- 게시글 보기 페이지에서 다운로드 횟수 표시

---

## 🔧 설치 순서

### 1단계: 데이터베이스 업데이트
다음 스크립트들을 순서대로 실행하세요:

```
1. http://your-domain/update_db_likes.php
2. http://your-domain/update_db_reports.php
```

### 2단계: 방문자 추적 활성화
`inc/header.php` 파일 상단에 다음 코드 추가:
```php
<?php
// 방문자 추적
if (file_exists(__DIR__ . '/../inc/visit_tracker.php')) {
    require_once __DIR__ . '/../inc/visit_tracker.php';
}
?>
```

### 3단계: 관리자 메뉴 업데이트
`admin/common.php`의 메뉴에 다음 항목들을 추가:
- 📢 Notice Management (notice.php)
- 🚨 Report Management (reports.php)
- 📊 Visit Statistics (visit_stats.php)
- 🚫 IP Ban (ip_ban.php)

---

## 📝 사용 가이드

### 비밀번호 재설정
1. 사용자가 로그인 페이지에서 "비밀번호를 잊으셨나요?" 클릭
2. 이메일 주소 입력
3. 이메일로 재설정 링크 수신 (1시간 유효)
4. 링크 클릭 후 새 비밀번호 설정

### 게시글 추천
- 게시글 보기 페이지에 "👍 추천 (0)" 버튼 추가
- 클릭 시 AJAX로 추천 처리
- 이미 추천한 경우 "추천 취소" 가능

### 신고 기능
- 게시글/댓글에 "🚨 신고" 버튼 추가
- 신고 사유 선택 및 상세 설명 입력
- 관리자가 검토 후 처리/기각

### 방문자 통계
- 관리자 페이지에서 실시간 통계 확인
- 기간별 필터링 (7일/30일/90일/1년)
- 차트 및 테이블로 시각화

---

## 🎨 UI 통합 가이드

### view.php (게시글 보기)에 추천 버튼 추가
```php
<div class="post-actions">
    <button id="like-btn" class="btn-like" data-bo-table="<?php echo $bo_table; ?>" data-wr-id="<?php echo $wr_id; ?>">
        👍 <span id="like-count"><?php echo $post['wr_likes'] ?? 0; ?></span>
    </button>
    <button class="btn-report" onclick="reportPost('<?php echo $bo_table; ?>', <?php echo $wr_id; ?>)">
        🚨 신고
    </button>
</div>

<script>
// 추천 기능
document.getElementById('like-btn').addEventListener('click', function() {
    const boTable = this.dataset.boTable;
    const wrId = this.dataset.wrId;
    
    fetch('like_post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=like&bo_table=${boTable}&wr_id=${wrId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('like-count').textContent = data.likes;
            alert(data.message);
        } else {
            alert(data.message);
        }
    });
});

// 신고 기능
function reportPost(boTable, wrId) {
    const reason = prompt('신고 사유를 입력하세요:');
    if (!reason) return;
    
    fetch('report.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `report_type=post&bo_table=${boTable}&target_id=${wrId}&reason=${encodeURIComponent(reason)}`
    })
    .then(res => res.json())
    .then(data => alert(data.message));
}
</script>
```

---

## 🔒 보안 고려사항

### 비밀번호 재설정
- 토큰은 1시간 후 자동 만료
- 토큰은 1회용 (사용 후 무효화)
- 이메일 주소 존재 여부를 노출하지 않음 (보안)

### 신고 시스템
- 로그인한 사용자만 신고 가능
- 중복 신고 방지
- 관리자만 신고 내역 조회 가능

### 방문자 추적
- 봇/크롤러 자동 제외
- IP 기반 순 방문자 집계
- 개인정보는 저장하지 않음

---

## 📚 추가 개선 제안

### 향후 추가 가능한 기능
1. **이메일 인증 시스템** - 회원가입 시 이메일 인증
2. **인기 게시물** - 조회수/추천수 기반 인기 게시물 표시
3. **최근 게시물 위젯** - 메인 페이지에 최근 게시물 표시
4. **댓글 추천** - 게시글뿐만 아니라 댓글도 추천 가능
5. **알림 시스템** - 댓글/답글 알림 기능
6. **북마크** - 게시글 북마크/즐겨찾기 기능
7. **태그 시스템** - 게시글 태그 및 태그별 검색

---

## ✅ 체크리스트

- [x] 비밀번호 재설정 시스템
- [x] 게시글 추천/좋아요 기능
- [x] 신고 시스템
- [x] 방문자 통계
- [x] IP 차단 시스템
- [x] 공지사항 관리
- [x] 다운로드 카운터
- [ ] UI 통합 (view.php, list.php 등)
- [ ] 언어 파일 업데이트 (ko.php, en.php, ja.php, zh.php)
- [ ] 관리자 메뉴 업데이트

---

## 📞 문의 및 지원

추가 기능이나 개선사항이 필요하시면 GitHub Issues를 통해 문의해주세요.

**MicroBoard v1.0.0** - Made with ❤️
