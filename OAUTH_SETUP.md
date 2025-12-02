# OAuth 소셜 로그인 설정 가이드

MicroBoard는 Google, LINE, Apple 소셜 로그인을 지원합니다.

## 설정 방법

### 1. 데이터베이스 업데이트

기존 설치에 OAuth 기능을 추가하려면 다음 파일을 실행하세요:
```
http://your-domain.com/update_db_oauth.php
```

### 2. 관리자 페이지에서 설정

1. 관리자로 로그인
2. 관리자 페이지 → OAuth 소셜 로그인 설정
3. 각 제공자의 API 키 입력 및 활성화

**중요**: 소셜 로그인 버튼은 다음 조건을 모두 만족할 때만 표시됩니다:
- ✅ 클라이언트 ID가 입력되어 있음
- ✅ 클라이언트 비밀키가 입력되어 있음
- ✅ "사용 활성화" 체크박스가 선택되어 있음

관리자 페이지에서 각 제공자의 설정 상태를 확인할 수 있습니다:
- **✓ 설정 완료** (녹색): 모든 설정이 완료되고 활성화됨
- **⚠ 미설정** (빨간색): 설정이 완료되지 않았거나 비활성화됨

## Google OAuth 설정

### 1. Google Cloud Console 설정
1. [Google Cloud Console](https://console.cloud.google.com/) 접속
2. 새 프로젝트 생성 또는 기존 프로젝트 선택
3. "API 및 서비스" → "사용자 인증 정보" 이동
4. "사용자 인증 정보 만들기" → "OAuth 2.0 클라이언트 ID" 선택
5. 애플리케이션 유형: "웹 애플리케이션" 선택
6. 승인된 리디렉션 URI 추가:
   ```
   http://your-domain.com/oauth_callback.php
   ```
7. 클라이언트 ID와 클라이언트 보안 비밀 복사

### 2. MicroBoard 설정
- 클라이언트 ID: Google에서 받은 클라이언트 ID 입력
- 클라이언트 비밀키: Google에서 받은 클라이언트 보안 비밀 입력
- 사용 활성화 체크

## LINE Login 설정

### 1. LINE Developers Console 설정
1. [LINE Developers Console](https://developers.line.biz/console/) 접속
2. 새 Provider 생성 (없는 경우)
3. 새 Channel 생성 (Channel type: LINE Login)
4. "LINE Login" 탭에서 설정:
   - Callback URL 추가:
     ```
     http://your-domain.com/oauth_callback.php
     ```
5. "Basic settings" 탭에서 Channel ID와 Channel secret 확인

### 2. MicroBoard 설정
- 클라이언트 ID: LINE Channel ID 입력
- 클라이언트 비밀키: LINE Channel Secret 입력
- 사용 활성화 체크

## Apple Sign In 설정

### 1. Apple Developer 설정
1. [Apple Developer](https://developer.apple.com/account/) 접속
2. "Certificates, Identifiers & Profiles" 이동
3. "Identifiers" → "+" 버튼 클릭
4. "App IDs" 선택 후 "Sign in with Apple" 활성화
5. "Services IDs" 생성:
   - Identifier 입력 (예: com.yourcompany.microboard)
   - "Sign in with Apple" 활성화
   - Return URLs 설정:
     ```
     http://your-domain.com/oauth_callback.php
     ```
6. "Keys" 생성:
   - "Sign in with Apple" 활성화
   - Key ID와 Private Key 다운로드

### 2. MicroBoard 설정
- 클라이언트 ID: Apple Service ID 입력
- 클라이언트 비밀키: Apple Team ID 입력
- **참고**: Apple은 추가 설정이 필요합니다 (Key ID, Private Key 등)

## 콜백 URL

모든 OAuth 제공자에 다음 콜백 URL을 등록해야 합니다:
```
http://your-domain.com/oauth_callback.php
```

HTTPS를 사용하는 경우:
```
https://your-domain.com/oauth_callback.php
```

## 보안 고려사항

1. **HTTPS 사용 권장**: 프로덕션 환경에서는 반드시 HTTPS를 사용하세요.
2. **비밀키 보호**: 클라이언트 비밀키는 절대 공개하지 마세요.
3. **정기적인 키 갱신**: 보안을 위해 정기적으로 API 키를 갱신하세요.

## 문제 해결

### 로그인 버튼이 표시되지 않음
- 관리자 페이지에서 해당 제공자가 활성화되어 있는지 확인
- 클라이언트 ID와 비밀키가 올바르게 입력되었는지 확인

### "Invalid redirect URI" 오류
- OAuth 제공자 콘솔에서 콜백 URL이 정확히 등록되었는지 확인
- HTTP/HTTPS 프로토콜이 일치하는지 확인

### 사용자 정보를 가져올 수 없음
- API 키가 올바른지 확인
- OAuth 제공자의 API 사용량 제한을 확인

## 기술 세부사항

### 데이터베이스 테이블

**mb1_oauth_config**: OAuth 제공자 설정 저장
- provider: 제공자 이름 (google, line, apple)
- client_id: 클라이언트 ID
- client_secret: 클라이언트 비밀키
- enabled: 활성화 여부

**mb1_oauth_users**: OAuth 사용자 연동 정보
- mb_id: MicroBoard 사용자 ID
- provider: OAuth 제공자
- provider_user_id: 제공자의 사용자 ID
- created_at: 연동 생성 시간

### 파일 구조

- `inc/oauth.php`: OAuth 헬퍼 함수
- `oauth_callback.php`: OAuth 콜백 처리
- `admin/oauth.php`: OAuth 설정 관리 페이지
- `update_db_oauth.php`: 데이터베이스 마이그레이션 스크립트
