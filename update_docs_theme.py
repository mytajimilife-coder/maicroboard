import os
import re

def update_file(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # --- 기존 코드 정리 (중복 방지) ---
    # 1. 기존 Light Mode CSS 제거
    content = re.sub(r'/\* Light Mode Styles \*/.*?(?=</style>)', '', content, flags=re.DOTALL)
    
    # 2. 기존 HTML 요소 제거
    content = content.replace('<div class="sky-background"></div>', '')
    content = content.replace('<div class="clouds" id="clouds-container"></div>', '')
    content = re.sub(r'<button class="theme-toggle".*?</button>', '', content, flags=re.DOTALL)
    
    # 3. 기존 JS 제거
    content = re.sub(r'<script>\s*// 테마 관리.*?</script>', '', content, flags=re.DOTALL)
    
    # --- 새로운 코드 추가 ---

    # 1. CSS 추가
    css_code = """
        /* Light Mode Styles */
        body.light-mode {
            background: #87CEEB;
            color: #333;
        }
        body.light-mode .space-background {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s, visibility 0.5s;
        }
        body.light-mode .stars,
        body.light-mode .particle {
            display: none;
        }
        
        .sky-background {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, #2980b9 0%, #6dd5fa 50%, #ffffff 100%); /* Deep Sky */
            z-index: -1;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s, visibility 0.5s;
        }
        body.light-mode .sky-background {
            opacity: 1;
            visibility: visible;
        }
        
        /* Clouds */
        .clouds {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: -1;
            display: none;
        }
        body.light-mode .clouds {
            display: block;
        }
        
        .cloud {
            position: absolute;
            background: #fff;
            border-radius: 100px;
            filter: blur(8px);
            opacity: 0.9;
            animation: moveCloud linear infinite;
        }
        
        @keyframes moveCloud {
            0% { transform: translateX(-200px); }
            100% { transform: translateX(120vw); }
        }

        /* UI Colors for Light Mode */
        body.light-mode .cosmic-header {
            background: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        body.light-mode .cosmic-card,
        body.light-mode .feature-card,
        body.light-mode .admin-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0,0,0,0.1);
            color: #333;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        body.light-mode .cosmic-card:hover,
        body.light-mode .feature-card:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        body.light-mode h1, 
        body.light-mode h2, 
        body.light-mode h3,
        body.light-mode strong {
            color: #2c3e50;
            text-shadow: none;
        }
        body.light-mode p, 
        body.light-mode li {
            color: #555;
        }
        body.light-mode .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #333;
        }
        body.light-mode .nav-links a {
            color: #555;
        }
        body.light-mode .nav-links a:hover {
            color: #3498db;
            background: rgba(52, 152, 219, 0.1);
        }
        body.light-mode .footer {
            background: rgba(255, 255, 255, 0.9);
            border-top: 1px solid rgba(0,0,0,0.1);
            color: #666;
        }
        
        /* Theme Toggle Button */
        .theme-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        body.light-mode .theme-toggle {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0,0,0,0.1);
            color: #333;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .theme-toggle:hover {
            transform: scale(1.1);
        }
    """
    
    if '</style>' in content:
        content = content.replace('</style>', css_code + '\n</style>')
    else:
        content = content.replace('</head>', f'<style>{css_code}</style>\n</head>')

    # 2. HTML 구조 추가
    html_code = """
    <div class="sky-background"></div>
    <div class="clouds" id="clouds-container"></div>
    <button class="theme-toggle" id="theme-toggle" title="Toggle Theme">🌙</button>
    """
    
    if '<div class="space-background"></div>' in content:
        content = content.replace('<div class="space-background"></div>', '<div class="space-background"></div>' + html_code)
    elif '<body>' in content:
        content = content.replace('<body>', '<body>' + html_code)

    # 3. JS 추가
    js_code = """
    <script>
        // 테마 관리
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const cloudsContainer = document.getElementById('clouds-container');
        
        // 구름 생성 함수
        function createClouds() {
            if (cloudsContainer.children.length > 0) return;
            
            for (let i = 0; i < 8; i++) {
                const cloud = document.createElement('div');
                cloud.className = 'cloud';
                
                // 랜덤 크기 및 위치
                const width = 120 + Math.random() * 180;
                const height = width * 0.6;
                cloud.style.width = width + 'px';
                cloud.style.height = height + 'px';
                cloud.style.top = (Math.random() * 40) + '%';
                cloud.style.left = -width + 'px';
                
                // 랜덤 애니메이션 속도 및 지연
                const duration = 25 + Math.random() * 35;
                const delay = Math.random() * -20;
                cloud.style.animationDuration = duration + 's';
                cloud.style.animationDelay = delay + 's';
                
                cloudsContainer.appendChild(cloud);
            }
        }
        
        // 초기 테마 설정
        const savedTheme = localStorage.getItem('docs_theme');
        if (savedTheme === 'light') {
            body.classList.add('light-mode');
            themeToggle.textContent = '☀️';
            createClouds();
        } else {
            themeToggle.textContent = '🌙';
        }
        
        // 토글 이벤트
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('light-mode');
            
            if (body.classList.contains('light-mode')) {
                localStorage.setItem('docs_theme', 'light');
                themeToggle.textContent = '☀️';
                createClouds();
            } else {
                localStorage.setItem('docs_theme', 'dark');
                themeToggle.textContent = '🌙';
            }
        });
    </script>
    """
    
    if '</body>' in content:
        content = content.replace('</body>', js_code + '\n</body>')

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Updated {file_path}")

# docs 폴더 내의 모든 HTML 파일 처리
docs_dir = 'd:/microboard/docs'
for filename in os.listdir(docs_dir):
    if filename.endswith('.html'):
        update_file(os.path.join(docs_dir, filename))
