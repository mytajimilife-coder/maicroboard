<?php
define('IN_ADMIN', true);
require_once 'common.php';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config_data = [];
    
    // 포인트 설정
    $config_data['cf_use_point'] = isset($_POST['cf_use_point']) ? 1 : 0;
    $config_data['cf_write_point'] = intval($_POST['cf_write_point']);
    
    // 테마 설정
    $config_data['cf_theme'] = $_POST['cf_theme'] ?? 'light';
    $config_data['cf_bg_type'] = $_POST['cf_bg_type'] ?? 'color';
    $config_data['cf_bg_value'] = $_POST['cf_bg_value'] ?? '#ffffff';
    
    // 배경 이미지 업로드 처리
    if (isset($_FILES['cf_bg_image']) && $_FILES['cf_bg_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/bg/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['cf_bg_image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = 'bg_' . time() . '.' . $file_ext;
            $dest_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['cf_bg_image']['tmp_name'], $dest_path)) {
                // 이미지 업로드 성공 시 배경 값을 이미지 경로로 설정하고 타입을 이미지로 변경
                $config_data['cf_bg_type'] = 'image';
                $config_data['cf_bg_value'] = 'img/bg/' . $new_filename;
            }
        }
    }
    
    update_config($config_data);
    
    $success_message = $lang['settings_saved'];
}

// 현재 설정 가져오기
$config = get_config();

// 기본값 설정 (DB 업데이트 전일 경우 대비)
if (!isset($config['cf_theme'])) $config['cf_theme'] = 'light';
if (!isset($config['cf_bg_type'])) $config['cf_bg_type'] = 'color';
if (!isset($config['cf_bg_value'])) $config['cf_bg_value'] = '#ffffff';
?>

<h1><?php echo $lang['config_management']; ?></h1>

<?php if (isset($success_message)): ?>
<div style="padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
    <?php echo $success_message; ?>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" style="max-width: 800px;">
    
    <!-- 포인트 설정 -->
    <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;"><?php echo $lang['point_settings']; ?></h2>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['use_point']; ?>
            </label>
            <label style="display: inline-block; margin-right: 20px;">
                <input type="radio" name="cf_use_point" value="1" <?php echo $config['cf_use_point'] ? 'checked' : ''; ?>>
                <?php echo $lang['point_enabled']; ?>
            </label>
            <label style="display: inline-block;">
                <input type="radio" name="cf_use_point" value="0" <?php echo !$config['cf_use_point'] ? 'checked' : ''; ?>>
                <?php echo $lang['point_disabled']; ?>
            </label>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['write_point']; ?>
            </label>
            <input type="number" name="cf_write_point" value="<?php echo $config['cf_write_point']; ?>" 
                   style="width: 200px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <small style="display: block; margin-top: 5px; color: #666;">
                <?php echo $lang['point_description']; ?>
            </small>
        </div>
    </div>

    <!-- 테마 설정 -->
    <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">테마 설정 (Theme Settings)</h2>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">기본 테마 모드</label>
            <label style="display: inline-block; margin-right: 20px;">
                <input type="radio" name="cf_theme" value="light" <?php echo $config['cf_theme'] === 'light' ? 'checked' : ''; ?>>
                Light Mode (밝은 모드)
            </label>
            <label style="display: inline-block;">
                <input type="radio" name="cf_theme" value="dark" <?php echo $config['cf_theme'] === 'dark' ? 'checked' : ''; ?>>
                Dark Mode (어두운 모드)
            </label>
            <p style="margin-top: 5px; color: #666; font-size: 0.9em;">사용자가 처음 방문했을 때 적용될 기본 테마입니다.</p>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">배경 설정 (Background)</label>
            <div style="margin-bottom: 10px;">
                <label style="display: inline-block; margin-right: 20px;">
                    <input type="radio" name="cf_bg_type" value="color" <?php echo $config['cf_bg_type'] === 'color' ? 'checked' : ''; ?> onclick="toggleBgInput('color')">
                    단색/그라데이션 (Color/Gradient)
                </label>
                <label style="display: inline-block;">
                    <input type="radio" name="cf_bg_type" value="image" <?php echo $config['cf_bg_type'] === 'image' ? 'checked' : ''; ?> onclick="toggleBgInput('image')">
                    이미지 (Image)
                </label>
            </div>

            <!-- 색상 입력 -->
            <div id="bg_color_input" style="display: <?php echo $config['cf_bg_type'] === 'color' ? 'block' : 'none'; ?>;">
                <input type="text" name="cf_bg_value" id="cf_bg_value_color" value="<?php echo $config['cf_bg_type'] === 'color' ? htmlspecialchars($config['cf_bg_value']) : ''; ?>" 
                       placeholder="예: #ffffff 또는 linear-gradient(...)"
                       style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <p style="margin-top: 5px; color: #666; font-size: 0.9em;">
                    CSS 색상 코드(#ffffff) 또는 그라데이션(linear-gradient(...))을 입력하세요.<br>
                    기본값: <code>linear-gradient(to bottom, #87CEEB 0%, #B0E0E6 50%, #FFDAB9 100%)</code>
                </p>
            </div>

            <!-- 이미지 업로드 -->
            <div id="bg_image_input" style="display: <?php echo $config['cf_bg_type'] === 'image' ? 'block' : 'none'; ?>;">
                <?php if ($config['cf_bg_type'] === 'image' && !empty($config['cf_bg_value'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo htmlspecialchars($config['cf_bg_value']); ?>" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                        <p>현재 배경 이미지: <?php echo htmlspecialchars($config['cf_bg_value']); ?></p>
                    </div>
                <?php endif; ?>
                <input type="file" name="cf_bg_image" accept="image/*" style="padding: 5px;">
                <p style="margin-top: 5px; color: #666; font-size: 0.9em;">새 이미지를 업로드하면 기존 설정이 덮어씌워집니다.</p>
            </div>
        </div>
    </div>
    
    <button type="submit" class="btn" style="padding: 10px 20px;">
        <?php echo $lang['save']; ?>
    </button>
</form>

<script>
function toggleBgInput(type) {
    document.getElementById('bg_color_input').style.display = type === 'color' ? 'block' : 'none';
    document.getElementById('bg_image_input').style.display = type === 'image' ? 'block' : 'none';
    
    // 색상 모드로 전환 시, 입력 필드가 비어있지 않게 처리 (선택 사항)
    if (type === 'color') {
        const colorInput = document.getElementById('cf_bg_value_color');
        if (!colorInput.value) {
            // colorInput.value = '#ffffff';
        }
    }
}
</script>

</body>
</html>
