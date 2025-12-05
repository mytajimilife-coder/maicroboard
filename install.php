<?php
// DB 체크 건너뛰기 플래그 (config.php에서 사용)
define('SKIP_DB_CHECK', true);

session_start();

// DB 설정 상수 정의 (config.php 없이도 작동하도록)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'microboard');
}

// 이미 설치되었는지 확인 (DB 연결 테스트)
$already_installed = false;
if (file_exists('config.php')) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $test_pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        // 테이블 존재 확인
        $stmt = $test_pdo->query("SHOW TABLES LIKE 'mb1_member'");
        if ($stmt->rowCount() > 0) {
            $already_installed = true;
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        // 연결 실패 시 설치 계속 진행
        $already_installed = false;
    }
}

$error = '';
$success = '';

// 언어 파일 로드
if (isset($_POST['language'])) {
    $language = $_POST['language'];
} else {
    // 브라우저 언어 감지
    $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
    $language = in_array($browser_lang, ['ko', 'en', 'ja', 'zh']) ? $browser_lang : 'en';
}

$lang_file = "lang/{$language}.php";
if (file_exists($lang_file)) {
    $lang = require $lang_file;
} else {
    $lang = require 'lang/en.php';
}

// 설치 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    $language = $_POST['language'] ?? 'en';
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'microboard';
    $admin_username = $_POST['admin_username'] ?? 'admin';
    $admin_password = $_POST['admin_password'] ?? 'admin';
    $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
    $license_agreed = $_POST['license_agreed'] ?? '0';
    
    // 라이선스 동의 검증
    if ($license_agreed !== '1') {
        $error = $lang['input_required'];
    } else {
        // 입력값 검증
        if (empty($db_host) || empty($db_user) || empty($db_name) || empty($admin_username) || empty($admin_password)) {
            $error = $lang['input_required'];
        } elseif ($admin_password !== $admin_password_confirm) {
            $error = $lang['password_mismatch'];
        } elseif (strlen($admin_password) < 6) {
            $error = $lang['invalid_password'];
        } elseif (!in_array($language, ['ko', 'en', 'ja', 'zh'])) {
            $error = $lang['invalid_format'];
        } else {
            try {
                // 1. 데이터베이스 이름으로 직접 연결 시도
                $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
                try {
                    $pdo = new PDO($dsn, $db_user, $db_pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                } catch (PDOException $e) {
                    // 2. 연결 실패 시 DB 없이 연결 후 생성 시도
                    $dsn_no_db = "mysql:host={$db_host};charset=utf8mb4";
                    $pdo = new PDO($dsn_no_db, $db_user, $db_pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                    
                    // 데이터베이스 생성 시도
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo->exec("USE `{$db_name}`");
                }
                
                // 테이블 생성
                $sql = "
                    CREATE TABLE IF NOT EXISTS `mb1_board` (
                        `wr_id` int(11) NOT NULL AUTO_INCREMENT,
                        `wr_subject` varchar(255) NOT NULL,
                        `wr_content` longtext NOT NULL,
                        `wr_name` varchar(50) NOT NULL,
                        `wr_datetime` datetime NOT NULL,
                        `wr_hit` int(11) NOT NULL DEFAULT 0,
                        PRIMARY KEY (`wr_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_board_config` (
                        `bo_table` varchar(100) NOT NULL,
                        `bo_subject` varchar(255) NOT NULL,
                        `bo_admin` varchar(50) NOT NULL DEFAULT 'admin',
                        `bo_list_count` int(11) NOT NULL DEFAULT 15,
                        `bo_use_comment` tinyint(1) NOT NULL DEFAULT 0,
                        `bo_skin` varchar(50) NOT NULL DEFAULT 'default',
                        `bo_plugins` text,
                        PRIMARY KEY (`bo_table`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_member` (
                        `mb_id` varchar(50) NOT NULL,
                        `mb_password` varchar(255) NOT NULL,
                        `mb_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
                        `mb_point` int(11) NOT NULL DEFAULT 0,
                        `mb_level` tinyint(4) NOT NULL DEFAULT 1,
                        `mb_blocked` tinyint(1) NOT NULL DEFAULT 0,
                        `mb_blocked_reason` varchar(255) DEFAULT NULL,
                        `mb_leave_date` datetime DEFAULT NULL,
                        `oauth_provider` varchar(50) DEFAULT NULL,
                        PRIMARY KEY (`mb_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_board_file` (
                        `bf_no` int(11) NOT NULL AUTO_INCREMENT,
                        `wr_id` int(11) NOT NULL,
                        `bf_source` varchar(255) NOT NULL,
                        `bf_file` varchar(255) NOT NULL,
                        `bf_download` int(11) NOT NULL DEFAULT 0,
                        `bf_content` text,
                        `bf_filesize` int(11) NOT NULL DEFAULT 0,
                        `bf_datetime` datetime NOT NULL,
                        PRIMARY KEY (`bf_no`),
                        KEY `wr_id` (`wr_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_comment` (
                        `co_id` int(11) NOT NULL AUTO_INCREMENT,
                        `wr_id` int(11) NOT NULL,
                        `co_content` text NOT NULL,
                        `co_name` varchar(50) NOT NULL,
                        `co_datetime` datetime NOT NULL,
                        PRIMARY KEY (`co_id`),
                        KEY `wr_id` (`wr_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_config` (
                        `cf_use_point` tinyint(1) NOT NULL DEFAULT 0,
                        `cf_write_point` int(11) NOT NULL DEFAULT 0
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_point` (
                        `po_id` int(11) NOT NULL AUTO_INCREMENT,
                        `mb_id` varchar(50) NOT NULL,
                        `po_datetime` datetime NOT NULL,
                        `po_content` varchar(255) NOT NULL,
                        `po_point` int(11) NOT NULL,
                        `po_rel_table` varchar(50) DEFAULT NULL,
                        `po_rel_id` int(11) DEFAULT NULL,
                        `po_rel_action` varchar(50) DEFAULT NULL,
                        PRIMARY KEY (`po_id`),
                        KEY `mb_id` (`mb_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_oauth_config` (
                        `provider` varchar(50) NOT NULL,
                        `client_id` varchar(255) NOT NULL,
                        `client_secret` varchar(255) NOT NULL,
                        `enabled` tinyint(1) NOT NULL DEFAULT 0,
                        PRIMARY KEY (`provider`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_oauth_users` (
                        `mb_id` varchar(50) NOT NULL,
                        `provider` varchar(50) NOT NULL,
                        `provider_user_id` varchar(255) NOT NULL,
                        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`provider`, `provider_user_id`),
                        KEY `mb_id` (`mb_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    
                    CREATE TABLE IF NOT EXISTS `mb1_policy` (
                        `policy_type` varchar(50) NOT NULL,
                        `policy_title` varchar(255) NOT NULL,
                        `policy_content` longtext NOT NULL,
                        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`policy_type`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                
                $pdo->exec($sql);
                
                // 기본 관리자 사용자 생성
                $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO mb1_member (mb_id, mb_password, mb_level) VALUES (?, ?, 10)");
                $stmt->execute([$admin_username, $password_hash]);
                
                // 기본 게시판 생성
                $stmt = $pdo->prepare("INSERT INTO mb1_board_config (bo_table, bo_subject) VALUES ('free', ?)");
                $stmt->execute([$lang['free_board']]);
                
                // OAuth 기본 설정 추가
                $providers = ['google', 'line', 'apple'];
                foreach ($providers as $provider) {
                    $stmt = $pdo->prepare("INSERT INTO mb1_oauth_config (provider, client_id, client_secret, enabled) VALUES (?, '', '', 0)");
                    $stmt->execute([$provider]);
                }
                
                // 기본 설정 추가
                $stmt = $pdo->prepare("INSERT INTO mb1_config (cf_use_point, cf_write_point) VALUES (0, 0)");
                $stmt->execute();
                
                // 기본 이용약관 및 개인정보처리방침 추가
                $terms_content = '<h2>제1조 (목적)</h2>
<p>본 약관은 MicroBoard(이하 "회사"라 함)가 제공하는 서비스의 이용과 관련하여 회사와 회원 간의 권리, 의무 및 책임사항, 기타 필요한 사항을 규정함을 목적으로 합니다.</p>

<h2>제2조 (용어의 정의)</h2>
<p>이 약관에서 사용하는 용어의 정의는 다음과 같습니다.</p>
<ol>
    <li>"서비스"라 함은 구현되는 단말기와 상관없이 "회원"이 이용할 수 있는 회사 및 관련 제반 서비스를 의미합니다.</li>
    <li>"회원"이라 함은 회사의 "서비스"에 접속하여 이 약관에 따라 "회사"와 이용계약을 체결하고 "회사"가 제공하는 "서비스"를 이용하는 고객을 말합니다.</li>
    <li>"아이디(ID)"라 함은 "회원"의 식별과 "서비스" 이용을 위하여 "회원"이 정하고 "회사"가 승인하는 문자와 숫자의 조합을 의미합니다.</li>
    <li>"비밀번호"라 함은 "회원"이 부여 받은 "아이디와 일치되는 "회원"임을 확인하고 비밀보호를 위해 "회원" 자신이 정한 문자 또는 숫자의 조합을 의미합니다.</li>
</ol>

<h2>제3조 (약관의 게시와 개정)</h2>
<p>1. "회사"는 이 약관의 내용을 "회원"이 쉽게 알 수 있도록 서비스 초기 화면에 게시합니다.</p>
<p>2. "회사"는 "약관의 규제에 관한 법률", "정보통신망 이용촉진 및 정보보호 등에 관한 법률(이하 "정보통신망법")" 등 관련법을 위배하지 않는 범위에서 이 약관을 개정할 수 있습니다.</p>

<h2>제4조 (회원가입의 성립)</h2>
<p>1. 이용계약은 이용자가 약관의 내용에 대하여 동의를 한 다음 회원가입 신청을 하고 "회사"가 이러한 신청에 대하여 승낙함으로써 체결됩니다.</p>
<p>2. "회사"는 이용자의 신청에 대하여 서비스 이용을 승낙함을 원칙으로 합니다. 다만, 실명이 아니거나 타인의 명의를 이용한 경우, 허위의 정보를 기재한 경우 등에는 승낙하지 않을 수 있습니다.</p>

<h2>제5조 (개인정보보호 의무)</h2>
<p>"회사"는 "정보통신망법" 등 관계 법령이 정하는 바에 따라 "회원"의 개인정보를 보호하기 위해 노력합니다. 개인정보의 보호 및 사용에 대해서는 관련법 및 "회사"의 개인정보처리방침이 적용됩니다.</p>

<h2>제6조 (회원의 아이디 및 비밀번호의 관리에 대한 의무)</h2>
<p>1. "회원"의 "아이디"와 "비밀번호"에 관한 관리책임은 "회원"에게 있으며, 이를 제3자가 이용하도록 하여서는 안 됩니다.</p>
<p>2. "회사"는 "회원"의 "아이디"가 개인정보 유출 우려가 있거나, 반사회적 또는 미풍양속에 어긋나거나 "회사" 및 "회사"의 운영자로 오인한 우려가 있는 경우, 해당 "아이디"의 이용을 제한할 수 있습니다.</p>

<h2>제7조 (게시물의 저작권)</h2>
<p>1. "회원"이 "서비스" 내에 게시한 "게시물"의 저작권은 해당 게시물의 저작자에게 귀속됩니다.</p>
<p>2. "회원"이 "서비스" 내에 게시하는 "게시물"은 검색결과 내지 "서비스" 및 관련 프로모션 등에 노출될 수 있으며, 해당 노출을 위해 필요한 범위 내에서는 일부 수정, 복제, 편집되어 게시될 수 있습니다.</p>
<p>3. "회사"는 저작권법 규정을 준수하며, "회원"은 언제든지 고객센터 또는 "서비스" 내 관리기능을 통해 해당 게시물에 대해 삭제, 비공개 등의 조치를 취할 수 있습니다.</p>

<h2>제8조 (계약해제, 해지 등)</h2>
<p>1. "회원"은 언제든지 서비스 초기화면의 고객센터 또는 내 정보 관리 메뉴 등을 통하여 이용계약 해지 신청을 할 수 있으며, "회사"는 관련법 등이 정하는 바에 따라 이를 즉시 처리하여야 합니다.</p>
<p>2. "회원"이 계약을 해지할 경우, 관련법 및 개인정보처리방침에 따라 "회사"가 회원정보를 보유하는 경우를 제외하고는 해지 즉시 "회원"의 모든 데이터는 소멸됩니다.</p>';

                $privacy_content = '<h2>1. 개인정보의 처리 목적</h2>
<p>MicroBoard(이하 "회사")는 다음의 목적을 위하여 개인정보를 처리하고 있으며, 다음의 목적 이외의 용도로는 이용하지 않습니다.</p>
<ul>
    <li>회원 가입 의사 확인, 회원제 서비스 제공에 따른 본인 식별/인증, 회원자격 유지/관리, 서비스 부정이용 방지, 각종 고지/통지, 고충처리 등</li>
</ul>

<h2>2. 개인정보의 처리 및 보유 기간</h2>
<p>1. "회사"는 법령에 따른 개인정보 보유/이용기간 또는 정보주체로부터 개인정보를 수집 시에 동의 받은 개인정보 보유, 이용기간 내에서 개인정보를 처리, 보유합니다.</p>
<p>2. 각각의 개인정보 처리 및 보유 기간은 다음과 같습니다.</p>
<ul>
    <li>회원 가입 및 관리 : 회원 탈퇴 시까지</li>
    <li>다만, 다음의 사유에 해당하는 경우에는 해당 사유 종료 시까지
        <ul>
            <li>관계 법령 위반에 따른 수사, 조사 등이 진행 중인 경우에는 해당 수사, 조사 종료 시까지</li>
            <li>서비스 이용에 따른 채권, 채무관계 잔존 시에는 해당 채권, 채무관계 정산 시까지</li>
        </ul>
    </li>
</ul>

<h2>3. 정보주체와 법정대리인의 권리/의무 및 그 행사방법</h2>
<p>이용자는 개인정보주체로서 다음과 같은 권리를 행사할 수 있습니다.</p>
<ol>
    <li>개인정보 열람요구</li>
    <li>오류 등이 있을 경우 정정 요구</li>
    <li>삭제요구</li>
    <li>처리정지 요구</li>
</ol>

<h2>4. 처리하는 개인정보의 항목 작성</h2>
<p>"회사"는 다음의 개인정보 항목을 처리하고 있습니다.</p>
<ul>
    <li>수집항목 : 아이디, 비밀번호, 접속 로그, 쿠키, 접속 IP 정보</li>
</ul>

<h2>5. 개인정보의 파기</h2>
<p>"회사"는 원칙적으로 개인정보 처리목적이 달성된 경우에는 지체없이 해당 개인정보를 파기합니다. 파기의 절차, 기한 및 방법은 다음과 같습니다.</p>
<ul>
    <li>파기절차 : 이용자가 입력한 정보는 목적 달성 후 별도의 DB에 옮겨져(종이의 경우 별도의 서류) 내부 방침 및 기타 관련 법령에 따라 일정기간 저장된 후 혹은 즉시 파기됩니다.</li>
    <li>파기기한 : 이용자의 개인정보는 개인정보의 보유기간이 경과된 경우에는 보유기간의 종료일로부터 5일 이내에, 개인정보의 처리 목적 달성, 해당 서비스의 폐지, 사업의 종료 등 그 개인정보가 불필요하게 되었을 때에는 개인정보의 처리가 불필요한 것으로 인정되는 날로부터 5일 이내에 그 개인정보를 파기합니다.</li>
</ul>

<h2>6. 개인정보 보호책임자 작성</h2>
<p>"회사"는 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.</p>
<ul>
    <li>개인정보 보호책임자 : 관리자</li>
    <li>연락처 : admin@microboard.com</li>
</ul>';

                // 이용약관 및 개인정보처리방침 삽입 (기본 및 한국어)
                $sql = "INSERT INTO mb1_policy (policy_type, policy_title, policy_content, updated_at) VALUES (?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                
                // 이용약관
                $stmt->execute(['terms', '이용약관', $terms_content]);
                $stmt->execute(['terms_ko', '이용약관', $terms_content]);
                
                // 개인정보처리방침
                $stmt->execute(['privacy', '개인정보 보호정책', $privacy_content]);
                $stmt->execute(['privacy_ko', '개인정보 보호정책', $privacy_content]);
                
                // config.php 파일 업데이트 (DB 정보만 수정)
                $config_path = __DIR__ . '/config.php';
                $config_content = file_get_contents($config_path);
                
                // DB 설정 부분만 교체
                $config_content = preg_replace(
                    "/define\('DB_HOST',\s*'[^']*'\);/",
                    "define('DB_HOST', '{$db_host}');",
                    $config_content
                );
                $config_content = preg_replace(
                    "/define\('DB_USER',\s*'[^']*'\);/",
                    "define('DB_USER', '{$db_user}');",
                    $config_content
                );
                $config_content = preg_replace(
                    "/define\('DB_PASS',\s*'[^']*'\);/",
                    "define('DB_PASS', '" . addslashes($db_pass) . "');",
                    $config_content
                );
                $config_content = preg_replace(
                    "/define\('DB_NAME',\s*'[^']*'\);/",
                    "define('DB_NAME', '{$db_name}');",
                    $config_content
                );
                
                file_put_contents($config_path, $config_content);
                
                $success = $lang['installation_success'];
                
            } catch (Exception $e) {
                $error = $lang['db_conn_failed'] . ': ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo substr($language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MicroBoard - <?php echo $lang['install_title']; ?></title>
    <link rel="stylesheet" href="skin/default/style.css">
<link rel="stylesheet" href="skin/default/style.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #1f2937;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-color: #111827;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .install-container {
            width: 100%;
            max-width: 650px;
            margin: 2rem 1rem;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            background: var(--card-bg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }

        .install-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--secondary-color);
        }

        .install-title h1 {
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0 0 0.5rem 0;
            background: linear-gradient(135deg, var(--primary-color), #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin: 2rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.95rem;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.95rem;
            background-color: #f9fafb;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn {
            background: var(--primary-color);
            color: white;
            padding: 0.85rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s, transform 0.1s;
        }
        
        .btn:hover {
            background: var(--primary-hover);
        }

        .btn:active {
            transform: translateY(1px);
        }
        
        .btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            border-left: 4px solid #ef4444;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error::before {
            content: "⚠️";
        }
        
        .success {
            background: #dcfce7;
            color: #15803d;
            padding: 2rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid #bbf7d0;
        }
        
        .language-selector {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .language-options {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .language-option {
            padding: 0.6rem 1.2rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .language-option:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: #f5f3ff;
        }
        
        .language-option.selected {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: var(--shadow);
        }
        
        .license-agreement {
            margin-top: 1.5rem;
            padding: 1.25rem;
            background: #f9fafb;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            text-align: center;
        }

        .license-agreement input[type="checkbox"] {
            accent-color: var(--primary-color);
            transform: scale(1.1);
        }
        
        .success-message a {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 600;
        }
        
        .success-message a:hover {
            background: var(--primary-hover);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            background-color: var(--card-bg);
            margin: 5vh auto;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 700px;
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }
        
        .modal-header {
            padding: 1.25rem 1.5rem;
            background: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--secondary-color);
        }
        
        .close {
            color: var(--text-light);
            font-size: 1.75rem;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            width: 2rem;
            height: 2rem;
            line-height: 2rem;
            text-align: center;
            border-radius: 50%;
            transition: background 0.2s;
        }
        
        .close:hover {
            background: rgba(0,0,0,0.05);
            color: var(--text-color);
        }
        
        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        
        .modal-body pre {
            background: #1f2937;
            color: #e5e7eb;
            padding: 1.5rem;
            border-radius: var(--radius);
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            text-align: right;
            background: var(--bg-color);
        }

        @media (max-width: 640px) {
            .install-container {
                margin: 0;
                border-radius: 0;
                padding: 1.5rem;
                min-height: 100vh;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-title">
            <h1>MicroBoard</h1>
            <p style="color: var(--text-light); margin: 0;"><?php echo $lang['install_title']; ?></p>
        </div>

        <div class="language-selector">
            <form method="post" style="display: inline;">
                <input type="hidden" name="language" id="selected_language" value="<?php echo htmlspecialchars($language); ?>">
                <div class="language-options">
                    <div class="language-option <?php echo $language === 'ko' ? 'selected' : ''; ?>" data-lang="ko">
                        🇰🇷 한국어
                    </div>
                    <div class="language-option <?php echo $language === 'en' ? 'selected' : ''; ?>" data-lang="en">
                        🇬🇧 English
                    </div>
                    <div class="language-option <?php echo $language === 'ja' ? 'selected' : ''; ?>" data-lang="ja">
                        🇯🇵 日本語
                    </div>
                    <div class="language-option <?php echo $language === 'zh' ? 'selected' : ''; ?>" data-lang="zh">
                        🇨🇳 中文
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success success-message">
                <h3 style="margin: 0 0 1rem 0; color: #15803d;">🎉 Installation Complete!</h3>
                <p><?php echo htmlspecialchars($success); ?></p>
                <a href="index.php"><?php echo $lang['go_to_main']; ?></a>
            </div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" value="install">
                <input type="hidden" name="language" value="<?php echo htmlspecialchars($language); ?>">
                <input type="hidden" name="license_agreed" id="license_agreed" value="0">
                
                <div class="section-title"><?php echo $lang['db_settings']; ?></div>
                <div class="form-group">
                    <label for="db_host"><?php echo $lang['db_host']; ?></label>
                    <input type="text" name="db_host" id="db_host" value="localhost" placeholder="e.g., localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user"><?php echo $lang['username']; ?></label>
                    <input type="text" name="db_user" id="db_user" value="root" placeholder="e.g., root" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass"><?php echo $lang['password']; ?></label>
                    <input type="password" name="db_pass" id="db_pass" value="" placeholder="DB Password">
                </div>
                
                <div class="form-group">
                    <label for="db_name"><?php echo $lang['db_name']; ?></label>
                    <input type="text" name="db_name" id="db_name" value="microboard" placeholder="e.g., microboard" required>
                </div>
                
                <div class="section-title"><?php echo $lang['admin_settings']; ?></div>
                <div class="form-group">
                    <label for="admin_username"><?php echo $lang['username']; ?></label>
                    <input type="text" name="admin_username" id="admin_username" value="admin" placeholder="Admin ID" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password"><?php echo $lang['password']; ?></label>
                    <input type="password" name="admin_password" id="admin_password" placeholder="Min. 6 characters" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password_confirm"><?php echo $lang['password_confirm']; ?></label>
                    <input type="password" name="admin_password_confirm" id="admin_password_confirm" placeholder="Confirm Password" required>
                </div>
                
                <div class="license-agreement">
                    <input type="checkbox" id="license-checkbox" required>
                    <label for="license-checkbox">
                        <strong><?php echo $lang['license_agreement']; ?></strong><br>
                        <?php echo $lang['agree_message']; ?>
                        <br>
                        <a href="#" onclick="showLicense(); return false;" style="color: #007bff; text-decoration: underline;">
                            <?php echo isset($lang['view_license']) ? $lang['view_license'] : 'View License'; ?>
                        </a>
                    </label>
                </div>
                
                <button type="submit" class="btn" id="install-btn" disabled><?php echo $lang['install_btn']; ?></button>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        // 언어 선택
        document.querySelectorAll('.language-option').forEach(option => {
            option.addEventListener('click', function() {
                const lang = this.getAttribute('data-lang');
                
                // 선택 표시 제거
                document.querySelectorAll('.language-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // 선택 표시 추가
                this.classList.add('selected');
                
                // 폼에 언어 설정
                document.getElementById('selected_language').value = lang;
                
                // 폼 자동 제출
                document.querySelector('.language-selector form').submit();
            });
        });
        
        // 체크박스 변경 감지
        const licenseCheckbox = document.getElementById('license-checkbox');
        const installBtn = document.getElementById('install-btn');
        
        if (licenseCheckbox && installBtn) {
            licenseCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('license_agreed').value = '1';
                    installBtn.disabled = false;
                } else {
                    document.getElementById('license_agreed').value = '0';
                    installBtn.disabled = true;
                }
            });
        }
        
        // 폼 검증
        const installForm = document.querySelector('form[method="post"]');
        if (installForm && installForm.querySelector('input[name="action"]')) {
            installForm.addEventListener('submit', function(e) {
                const password = document.getElementById('admin_password').value;
                const confirmPassword = document.getElementById('admin_password_confirm').value;
                const licenseAgreed = document.getElementById('license_agreed').value;
                
                if (licenseAgreed !== '1') {
                    e.preventDefault();
                    alert('<?php echo $lang['input_required']; ?>');
                    return false;
                }
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('<?php echo $lang['password_mismatch']; ?>');
                    return false;
                }
            });
        }
        
        // 라이센스 모달 함수
        function showLicense() {
            document.getElementById('license-modal').style.display = 'block';
        }
        
        function closeLicense() {
            document.getElementById('license-modal').style.display = 'none';
        }
        
        // 모달 외부 클릭 시 닫기
        window.onclick = function(event) {
            const modal = document.getElementById('license-modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    
    <!-- License Modal -->
    <div id="license-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>MIT License</h2>
                <button class="close" onclick="closeLicense()">&times;</button>
            </div>
            <div class="modal-body">
                <pre><?php
                $license_file = __DIR__ . '/LICENSE';
                if (file_exists($license_file)) {
                    echo htmlspecialchars(file_get_contents($license_file));
                } else {
                    echo "MIT License\n\n";
                    echo "Copyright (c) 2025 YECHANHO\n\n";
                    echo "Permission is hereby granted, free of charge, to any person obtaining a copy\n";
                    echo "of this software and associated documentation files (the \"Software\"), to deal\n";
                    echo "in the Software without restriction, including without limitation the rights\n";
                    echo "to use, copy, modify, merge, publish, distribute, sublicense, and/or sell\n";
                    echo "copies of the Software, and to permit persons to whom the Software is\n";
                    echo "furnished to do so, subject to the following conditions:\n\n";
                    echo "The above copyright notice and this permission notice shall be included in all\n";
                    echo "copies or substantial portions of the Software.\n\n";
                    echo "THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR\n";
                    echo "IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,\n";
                    echo "FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE\n";
                    echo "AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER\n";
                    echo "LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,\n";
                    echo "OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE\n";
                    echo "SOFTWARE.";
                }
                ?></pre>
            </div>
            <div class="modal-footer">
                <button onclick="closeLicense()">Close</button>
            </div>
        </div>
    </div>
</body>
</html>
