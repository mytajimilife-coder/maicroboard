    </main>
    
    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> MicroBoard v<?php echo MICROBOARD_VERSION; ?>. <?php echo $lang['all_rights_reserved'] ?? 'All rights reserved.'; ?></p>
        </div>
    </footer>
    
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
                    if (!confirm('<?php echo $lang['delete_confirm'] ?? '정말 삭제하시겠습니까?'; ?>')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
