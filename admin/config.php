<?php
define('IN_ADMIN', true);
require_once 'common.php';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cf_use_point = isset($_POST['cf_use_point']) ? 1 : 0;
    $cf_write_point = intval($_POST['cf_write_point']);
    
    update_config([
        'cf_use_point' => $cf_use_point,
        'cf_write_point' => $cf_write_point
    ]);
    
    $success_message = $lang['settings_saved'];
}

// 현재 설정 가져오기
$config = get_config();
?>

<h1><?php echo $lang['config_management']; ?></h1>

<?php if (isset($success_message)): ?>
<div style="padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
    <?php echo $success_message; ?>
</div>
<?php endif; ?>

<form method="post" style="max-width: 600px;">
    <h2><?php echo $lang['point_settings']; ?></h2>
    
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
    
    <button type="submit" class="btn" style="padding: 10px 20px;">
        <?php echo $lang['save']; ?>
    </button>
</form>

</body>
</html>
