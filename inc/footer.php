    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['language'])) {
        $selected_lang = $_POST['language'];
        if (in_array($selected_lang, ['ko', 'en', 'ja', 'zh'])) {
            $_SESSION['lang'] = $selected_lang;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    ?>
    
    <link rel="stylesheet" href="../skin/inc/footer.css">
    <link rel="stylesheet" href="../skin/inc/content.css">
    
    <script>
        // 기본 자바스크립트 기능
        function confirmAction(message) {
            return confirm(message);
        }
        
        // 폼 제출 확인
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[action*="delete"]');
            deleteForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!confirm('<?php echo $lang['delete_confirm']; ?>')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
