<style>
.board-container {
  background: var(--bg-color);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  padding: 2rem;
  margin-bottom: 2rem;
}

.board-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.board-header h2 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--secondary-color);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.board-header h2::before {
  content: '';
  display: block;
  width: 6px;
  height: 24px;
  background: linear-gradient(to bottom, var(--primary-color), var(--primary-dark));
  border-radius: 3px;
}

.search-form {
  margin-bottom: 2rem;
  display: flex;
  justify-content: flex-end;
}

.search-box {
  display: flex;
  gap: 0.5rem;
  background: var(--bg-secondary);
  padding: 0.5rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
}

.search-box select {
  border: none;
  background: transparent;
  font-size: 0.9rem;
  color: var(--text-color);
  cursor: pointer;
  padding-right: 1.5rem;
  outline: none;
}

.search-box input {
  border: none;
  background: transparent;
  font-size: 0.9rem;
  color: var(--text-color);
  width: 200px;
  outline: none;
}

.search-box input::placeholder {
  color: var(--text-light);
}

.search-btn {
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--radius);
  padding: 0 1rem;
  cursor: pointer;
  transition: var(--transition);
}

.search-btn:hover {
  background: var(--primary-dark);
}

.board-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-bottom: 2rem;
}

.board-table th {
  padding: 1rem;
  font-weight: 600;
  color: var(--text-light);
  border-bottom: 2px solid var(--border-color);
  text-align: center;
  font-size: 0.9rem;
}

.board-table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-color);
  text-align: center;
  font-size: 0.95rem;
  transition: background-color 0.2s;
}

.board-table tr:hover td {
  background-color: var(--bg-secondary);
}

.board-table .subject {
  text-align: left;
  font-weight: 500;
}

.board-table .subject a {
  text-decoration: none;
  color: var(--secondary-color);
  display: block;
  transition: color 0.2s;
}

.board-table .subject a:hover {
  color: var(--primary-color);
}

.board-table .subject .new-badge {
  display: inline-block;
  font-size: 0.7rem;
  color: var(--success-color);
  background: rgba(16, 185, 129, 0.1);
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  margin-left: 0.5rem;
  font-weight: 700;
}

.writer-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.writer-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--bg-tertiary), var(--border-color));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: var(--text-muted);
}

.mobile-list {
  display: none;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 2rem;
}

.page-link {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 36px;
  height: 36px;
  padding: 0 0.5rem;
  border-radius: var(--radius);
  text-decoration: none;
  color: var(--text-color);
  font-weight: 500;
  border: 1px solid transparent;
  transition: var(--transition);
}

.page-link:hover {
  background: var(--bg-secondary);
  border-color: var(--border-color);
}

.page-link.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  box-shadow: var(--shadow-sm);
}

.empty-list {
  padding: 4rem !important;
  color: var(--text-light) !important;
  background: var(--bg-secondary);
  border-radius: var(--radius);
}

@media (max-width: 768px) {
  .board-table {
    display: none;
  }
  
  .mobile-list {
    display: block;
  }
  
  .mobile-item {
    background: var(--bg-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1rem;
    margin-bottom: 0.75rem;
    box-shadow: var(--shadow-sm);
  }
  
  .mobile-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
    color: var(--text-light);
  }
  
  .mobile-subject {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    display: block;
    color: var(--secondary-color);
    text-decoration: none;
  }
  
  .mobile-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: var(--text-muted);
  }
}
</style>

<div class="content-wrapper">
    <div class="board-container">
        <div class="board-header">
            <h2><?php echo $board_config['bo_subject']; ?></h2>
            <div class="board-actions">
                <a href="write.php?bo_table=<?php echo $bo_table; ?>" class="btn">
                    <span>✏️</span> <?php echo $lang['write']; ?>
                </a>
            </div>
        </div>

        <!-- 검색 폼 -->
        <div class="search-form">
            <form action="list.php" method="get" class="search-box">
                <input type="hidden" name="bo_table" value="<?php echo htmlspecialchars($bo_table); ?>">
                <select name="sfl">
                    <option value="wr_subject" <?php echo $sfl === 'wr_subject' ? 'selected' : ''; ?>><?php echo $lang['subject']; ?></option>
                    <option value="wr_content" <?php echo $sfl === 'wr_content' ? 'selected' : ''; ?>><?php echo $lang['content']; ?></option>
                    <option value="wr_name" <?php echo $sfl === 'wr_name' ? 'selected' : ''; ?>><?php echo $lang['writer']; ?></option>
                </select>
                <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" placeholder="<?php echo $lang['search_term']; ?>">
                <button type="submit" class="search-btn">🔍</button>
            </form>
        </div>

        <!-- PC용 테이블 리스트 -->
        <table class="board-table">
            <thead>
                <tr>
                    <th width="60"><?php echo $lang['num']; ?></th>
                    <th><?php echo $lang['subject']; ?></th>
                    <th width="120"><?php echo $lang['writer']; ?></th>
                    <th width="100"><?php echo $lang['date']; ?></th>
                    <th width="80"><?php echo $lang['hit']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?>
                <tr>
                    <td colspan="5" class="empty-list">
                        <div style="text-align: center;">
                            <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">📭</span>
                            <?php echo $lang['no_posts']; ?>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($list as $post): ?>
                    <tr onclick="location.href='view.php?id=<?php echo $post['wr_id']; ?>&bo_table=<?php echo $bo_table; ?>'" style="cursor: pointer;">
                        <td><span style="color: var(--text-light);"><?php echo $post['num']; ?></span></td>
                        <td class="subject">
                            <a href="view.php?id=<?php echo $post['wr_id']; ?>&bo_table=<?php echo $bo_table; ?>">
                                <?php echo $post['wr_subject']; ?>
                                <?php 
                                // 최근 24시간 이내 새 글 배지
                                $post_time = strtotime($post['wr_datetime']);
                                if (time() - $post_time < 24 * 3600) {
                                    echo '<span class="new-badge">N</span>';
                                }
                                ?>
                            </a>
                        </td>
                        <td>
                            <div class="writer-info">
                                <span class="writer-avatar">👤</span>
                                <?php echo $post['wr_name']; ?>
                            </div>
                        </td>
                        <td style="color: var(--text-light); font-size: 0.85rem;">
                            <?php echo date('Y.m.d', strtotime($post['wr_datetime'])); ?>
                        </td>
                        <td style="color: var(--text-light); font-size: 0.85rem;">
                            <?php echo number_format($post['wr_hit']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- 모바일용 리스트 -->
        <div class="mobile-list">
            <?php if (empty($list)): ?>
                <div class="empty-list" style="text-align: center;">
                    <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">📭</span>
                    <?php echo $lang['no_posts']; ?>
                </div>
            <?php else: ?>
                <?php foreach ($list as $post): ?>
                <div class="mobile-item" onclick="location.href='view.php?id=<?php echo $post['wr_id']; ?>&bo_table=<?php echo $bo_table; ?>'">
                    <div class="mobile-item-header">
                        <span>No. <?php echo $post['num']; ?></span>
                        <span><?php echo date('Y.m.d', strtotime($post['wr_datetime'])); ?></span>
                    </div>
                    <a href="view.php?id=<?php echo $post['wr_id']; ?>&bo_table=<?php echo $bo_table; ?>" class="mobile-subject">
                        <?php echo $post['wr_subject']; ?>
                        <?php 
                        if (time() - strtotime($post['wr_datetime']) < 24 * 3600) {
                            echo '<span class="new-badge">N</span>';
                        }
                        ?>
                    </a>
                    <div class="mobile-meta">
                        <span>👤 <?php echo $post['wr_name']; ?></span>
                        <span>👁️ <?php echo number_format($post['wr_hit']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 페이지네이션 -->
        <div class="pagination">
            <?php
            $qstr = '&bo_table=' . $bo_table . '&sfl=' . $sfl . '&stx=' . $stx;
            if ($page > 1) {
                echo '<a href="list.php?page=1' . $qstr . '" class="page-link" title="First">&laquo;</a>';
                echo '<a href="list.php?page=' . ($page - 1) . $qstr . '" class="page-link" title="Prev">&lt;</a>';
            }
            
            $start_page = max(1, $page - 4);
            $end_page = min($total_pages, $page + 4);
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<a href="list.php?page=' . $i . $qstr . '" class="page-link ' . $active . '">' . $i . '</a>';
            }
            
            if ($page < $total_pages) {
                echo '<a href="list.php?page=' . ($page + 1) . $qstr . '" class="page-link" title="Next">&gt;</a>';
                echo '<a href="list.php?page=' . $total_pages . $qstr . '" class="page-link" title="Last">&raquo;</a>';
            }
            ?>
        </div>
    </div>
</div>
