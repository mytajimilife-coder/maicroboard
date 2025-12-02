<div class="write-form">
    <h2><?php echo $page_title; ?></h2>
    <form action="write.php<?php echo $id ? '?id=' . $id : ''; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title"><?php echo $lang['subject']; ?></label>
            <input type="text" id="title" name="title" value="<?php echo $post['wr_subject']; ?>" required>
        </div>
        <div class="form-group">
            <label for="content"><?php echo $lang['content']; ?></label>
            <textarea id="summernote" name="content"><?php echo $post['wr_content']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label>파일 첨부</label>
            <input type="file" name="bf_file[]" multiple>
            <p class="help-block" style="font-size: 0.9em; color: #888; margin-top: 5px;">여러 파일을 선택할 수 있습니다.</p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn"><?php echo $lang['save']; ?></button>
            <a href="list.php" class="btn btn-secondary"><?php echo $lang['cancel']; ?></a>
        </div>
    </form>
</div>
