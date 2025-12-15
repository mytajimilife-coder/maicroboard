<?php
require_once 'config.php';

// 언어 파일 로드
$lang_code = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
$lang_file = "lang/{$lang_code}.php";
if (file_exists($lang_file)) {
    $lang = require $lang_file;
} else {
    $lang = require 'lang/ko.php';
}

// 관리자 확인
if (!isAdmin()) {
    die($lang['admin_only_exec']);
}

$db = getDB();

$terms_content = '
<h2>제1조 (목적)</h2>
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
<p>2. "회원"이 계약을 해지할 경우, 관련법 및 개인정보처리방침에 따라 "회사"가 회원정보를 보유하는 경우를 제외하고는 해지 즉시 "회원"의 모든 데이터는 소멸됩니다.</p>
';

$privacy_content = '
<h2>1. 개인정보의 처리 목적</h2>
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
</ul>
';

try {
    // 이용약관 업데이트 (기본 및 한국어)
    $stmt = $db->prepare("INSERT INTO mb1_policy (policy_type, policy_title, policy_content, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE policy_title = VALUES(policy_title), policy_content = VALUES(policy_content), updated_at = NOW()");
    $stmt->execute(['terms', '이용약관', $terms_content]);
    
    $stmt = $db->prepare("INSERT INTO mb1_policy (policy_type, policy_title, policy_content, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE policy_title = VALUES(policy_title), policy_content = VALUES(policy_content), updated_at = NOW()");
    $stmt->execute(['terms_ko', '이용약관', $terms_content]);

    // 개인정보처리방침 업데이트 (기본 및 한국어)
    $stmt = $db->prepare("INSERT INTO mb1_policy (policy_type, policy_title, policy_content, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE policy_title = VALUES(policy_title), policy_content = VALUES(policy_content), updated_at = NOW()");
    $stmt->execute(['privacy', '개인정보 보호정책', $privacy_content]);
    
    $stmt = $db->prepare("INSERT INTO mb1_policy (policy_type, policy_title, policy_content, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE policy_title = VALUES(policy_title), policy_content = VALUES(policy_content), updated_at = NOW()");
    $stmt->execute(['privacy_ko', '개인정보 보호정책', $privacy_content]);

    echo $lang['policies_updated'];
    echo "<br>" . $lang['file_auto_deleted'];
    echo "<meta http-equiv='refresh' content='3;url=policy.php'>";

    // 파일 자동 삭제
    register_shutdown_function(function() {
        @unlink(__FILE__);
    });

} catch (PDOException $e) {
    echo $lang['error_occurred'] . ": " . $e->getMessage();
}
?>
