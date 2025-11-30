<div class="post-view">
    <div class="post-header">
        <h2><?php echo $post['wr_subject']; ?></h2>
        <div class="post-meta">
            <span class="writer"><?php echo $post['wr_name']; ?></span>
            <span class="date"><?php echo $post['wr_datetime']; ?></span>
            <span class="hit">조회 <?php echo $post['wr_hit']; ?></span>
        </div>
    </div>

    <div class="post-content">
        <?php echo $post['wr_content']; ?>
    </div>

    <!-- 첨부파일 -->
    <?php if (!empty($files)): ?>
    <div class="post-files" style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
        <h3 style="font-size: 1.1em; margin-top: 0; margin-bottom: 10px;">첨부파일</h3>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($files as $file): ?>
            <li style="margin-bottom: 5px;">
                <a href="download.php?bf_no=<?php echo $file['bf_no']; ?>" style="text-decoration: none; color: #333;">
                    📁 <?php echo $file['bf_source']; ?> (<?php echo number_format($file['bf_filesize']); ?> byte)
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="post-actions">
        <a href="list.php" class="btn btn-secondary"><?php echo $lang['list']; ?></a>
        <?php if (isLoggedIn() && ($_SESSION['user'] === $post['wr_name'] || isAdmin())): ?>
        <a href="write.php?id=<?php echo $post['wr_id']; ?>" class="btn"><?php echo $lang['edit']; ?></a>
        <a href="view.php?id=<?php echo $post['wr_id']; ?>&action=delete&token=<?php echo $_SESSION['csrf_token']; ?>" 
           class="btn btn-danger" 
           onclick="return confirm('<?php echo $lang['delete_confirm']; ?>');"><?php echo $lang['delete']; ?></a>
        <?php endif; ?>
    </div>

    <!-- 댓글 -->
    <div class="comments-section" style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
        <h3>댓글</h3>
        
        <!-- 댓글 목록 -->
        <div class="comment-list">
            <?php foreach ($comments as $comment): ?>
            <div class="comment-item" style="padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                <div class="comment-meta" style="margin-bottom: 5px; font-size: 0.9em; color: #666;">
                    <strong><?php echo htmlspecialchars($comment['co_name']); ?></strong>
                    <span style="margin-left: 10px;"><?php echo $comment['co_datetime']; ?></span>
                    <?php if (isLoggedIn() && ($_SESSION['user'] === $comment['co_name'] || isAdmin())): ?>
                    <form action="comment_update.php" method="post" style="display: inline; margin-left: 10px;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="wr_id" value="<?php echo $post['wr_id']; ?>">
                        <input type="hidden" name="co_id" value="<?php echo $comment['co_id']; ?>">
                        <button type="submit" style="border: none; background: none; color: #e74c3c; cursor: pointer; padding: 0;" onclick="return confirm('삭제하시겠습니까?');">[삭제]</button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="comment-content">
                    <?php echo nl2br(htmlspecialchars($comment['co_content'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 댓글 작성 폼 -->
        <div class="comment-form" style="margin-top: 20px; background: #f9f9f9; padding: 20px; border-radius: 5px;">
            <form action="comment_update.php" method="post">
                <input type="hidden" name="action" value="insert">
                <input type="hidden" name="wr_id" value="<?php echo $post['wr_id']; ?>">
                <div class="form-group">
                    <textarea name="co_content" required placeholder="댓글을 입력하세요" style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>
                <div style="text-align: right; margin-top: 10px;">
                    <button type="submit" class="btn btn-sm">댓글 등록</button>
                </div>
            </form>
        </div>
    </div>
</div>
