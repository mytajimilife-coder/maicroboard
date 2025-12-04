// 다국어 지원 데이터
const translations = {
    en: {
        title: "MicroBoard - A Journey Through Digital Space",
        heroTitle: "MicroBoard",
        heroSubtitle: "A Journey Through Digital Space",
        versionBadge: "🌟 Version 1.0.0",
        aboutTitle: "🌌 About MicroBoard",
        aboutDescription: "MicroBoard is a lightweight, high-performance bulletin board system designed for simplicity and ease of use. Built with modern PHP standards, it offers a robust platform for community engagement without the complexity of larger systems.",
        featuresTitle: "✨ Key Features",
        feature1Title: "🚀 Lightweight & Fast",
        feature1Desc: "Optimized performance with minimal resource usage",
        feature2Title: "🌍 Multilingual Support",
        feature2Desc: "Built-in support for Korean, English, Japanese, and Chinese",
        feature3Title: "🔒 Secure by Design",
        feature3Desc: "CSRF protection, prepared statements, and session security",
        feature4Title: "📱 Responsive Design",
        feature4Desc: "Works perfectly on desktop, tablet, and mobile devices",
        oauthTitle: "🔐 OAuth Social Login",
        oauthDescription: "Seamless integration with popular OAuth providers:",
        oauthGoogle: "Google Login",
        oauthLine: "LINE Login",
        oauthApple: "Apple Sign In",
        quickstartTitle: "🚀 Quick Start",
        quickstartStep1: "Clone the repository",
        quickstartStep2: "Upload to your web server",
        quickstartStep3: "Run the installation wizard",
        quickstartStep4: "Configure your board settings",
        quickstartStep5: "Start building your community!",
        adminTitle: "⚙️ Admin Features",
        adminFeature1: "User Management",
        adminFeature2: "Board Configuration",
        adminFeature3: "OAuth Settings",
        adminFeature4: "Point System",
        licenseTitle: "📜 License",
        licenseDescription: "MicroBoard is open-source software licensed under the MIT License.",
        copyright: "© 2025 MicroBoard Team. All rights reserved.",
        footerTagline: "A Journey Through Digital Space",
        footerMadeWith: "Made with ❤️ by MicroBoard Team | Version 1.0.0",
        github: "🚀 GitHub",
        features: "⭐ Features",
        docs: "📖 Docs",
        download: "⬇️ Download"
    },
    ko: {
        title: "MicroBoard - 디지털 공간으로의 여정",
        heroTitle: "MicroBoard",
        heroSubtitle: "디지털 공간으로의 여정",
        versionBadge: "🌟 버전 1.0.0",
        aboutTitle: "🌌 MicroBoard 소개",
        aboutDescription: "MicroBoard는 단순성과 사용 편의성을 위해 설계된 경량 고성능 게시판 시스템입니다. 현대적인 PHP 표준으로 구축되어 대형 시스템의 복잡성 없이 커뮤니티 참여를 위한 강력한 플랫폼을 제공합니다.",
        featuresTitle: "✨ 주요 기능",
        feature1Title: "🚀 가볍고 빠름",
        feature1Desc: "최소한의 리소스 사용으로 최적화된 성능",
        feature2Title: "🌍 다국어 지원",
        feature2Desc: "한국어, 영어, 일본어, 중국어 기본 지원",
        feature3Title: "🔒 보안 설계",
        feature3Desc: "CSRF 보호, 준비된 명령문 및 세션 보안",
        feature4Title: "📱 반응형 디자인",
        feature4Desc: "데스크톱, 태블릿, 모바일 기기에서 완벽하게 작동",
        oauthTitle: "🔐 OAuth 소셜 로그인",
        oauthDescription: "인기 있는 OAuth 제공업체와의 원활한 통합:",
        oauthGoogle: "구글 로그인",
        oauthLine: "라인 로그인",
        oauthApple: "애플 로그인",
        quickstartTitle: "🚀 빠른 시작",
        quickstartStep1: "저장소 복제",
        quickstartStep2: "웹 서버에 업로드",
        quickstartStep3: "설치 마법사 실행",
        quickstartStep4: "게시판 설정 구성",
        quickstartStep5: "커뮤니티 구축 시작!",
        adminTitle: "⚙️ 관리자 기능",
        adminFeature1: "사용자 관리",
        adminFeature2: "게시판 구성",
        adminFeature3: "OAuth 설정",
        adminFeature4: "포인트 시스템",
        licenseTitle: "📜 라이선스",
        licenseDescription: "MicroBoard는 MIT 라이선스에 따라 라이선스가 부여된 오픈 소스 소프트웨어입니다.",
        copyright: "© 2025 MicroBoard Team. 모든 권리 보유.",
        footerTagline: "디지털 공간으로의 여정",
        footerMadeWith: "MicroBoard Team이 ❤️로 만듦 | 버전 1.0.0",
        github: "🚀 GitHub",
        features: "⭐ 기능",
        docs: "📖 문서",
        download: "⬇️ 다운로드"
    },
    ja: {
        title: "MicroBoard - デジタル空間への旅",
        heroTitle: "MicroBoard",
        heroSubtitle: "デジタル空間への旅",
        versionBadge: "🌟 バージョン 1.0.0",
        aboutTitle: "🌌 MicroBoardについて",
        aboutDescription: "MicroBoardは、シンプルさと使いやすさを重視して設計された軽量で高性能な掲示板システムです。最新のPHP標準で構築されており、大規模システムの複雑さなしにコミュニティエンゲージメントのための堅牢なプラットフォームを提供します。",
        featuresTitle: "✨ 主な機能",
        feature1Title: "🚀 軽量で高速",
        feature1Desc: "最小限のリソース使用で最適化されたパフォーマンス",
        feature2Title: "🌍 多言語サポート",
        feature2Desc: "韓国語、英語、日本語、中国語の組み込みサポート",
        feature3Title: "🔒 セキュアな設計",
        feature3Desc: "CSRF保護、プリペアドステートメント、セッションセキュリティ",
        feature4Title: "📱 レスポンシブデザイン",
        feature4Desc: "デスクトップ、タブレット、モバイルデバイスで完璧に動作",
        oauthTitle: "🔐 OAuthソーシャルログイン",
        oauthDescription: "人気のあるOAuthプロバイダーとのシームレスな統合:",
        oauthGoogle: "Googleログイン",
        oauthLine: "LINEログイン",
        oauthApple: "Appleでサインイン",
        quickstartTitle: "🚀 クイックスタート",
        quickstartStep1: "リポジトリをクローン",
        quickstartStep2: "Webサーバーにアップロード",
        quickstartStep3: "インストールウィザードを実行",
        quickstartStep4: "掲示板設定を構成",
        quickstartStep5: "コミュニティ構築を開始！",
        adminTitle: "⚙️ 管理者機能",
        adminFeature1: "ユーザー管理",
        adminFeature2: "掲示板設定",
        adminFeature3: "OAuth設定",
        adminFeature4: "ポイントシステム",
        licenseTitle: "📜 ライセンス",
        licenseDescription: "MicroBoardはMITライセンスの下でライセンスされたオープンソースソフトウェアです。",
        copyright: "© 2025 MicroBoard Team. 全著作権所有。",
        footerTagline: "デジタル空間への旅",
        footerMadeWith: "MicroBoard Teamが❤️で作成 | バージョン 1.0.0",
        github: "🚀 GitHub",
        features: "⭐ 機能",
        docs: "📖 ドキュメント",
        download: "⬇️ ダウンロード"
    },
    zh: {
        title: "MicroBoard - 数字空间之旅",
        heroTitle: "MicroBoard",
        heroSubtitle: "数字空间之旅",
        versionBadge: "🌟 版本 1.0.0",
        aboutTitle: "🌌 关于 MicroBoard",
        aboutDescription: "MicroBoard是一个轻量级、高性能的公告板系统，专为简单性和易用性而设计。采用现代PHP标准构建，为社区参与提供强大的平台，而无需大型系统的复杂性。",
        featuresTitle: "✨ 主要功能",
        feature1Title: "🚀 轻量快速",
        feature1Desc: "以最少的资源使用优化性能",
        feature2Title: "🌍 多语言支持",
        feature2Desc: "内置韩语、英语、日语和中文支持",
        feature3Title: "🔒 安全设计",
        feature3Desc: "CSRF保护、预处理语句和会话安全",
        feature4Title: "📱 响应式设计",
        feature4Desc: "在桌面、平板和移动设备上完美运行",
        oauthTitle: "🔐 OAuth社交登录",
        oauthDescription: "与流行的OAuth提供商无缝集成：",
        oauthGoogle: "Google登录",
        oauthLine: "LINE登录",
        oauthApple: "Apple登录",
        quickstartTitle: "🚀 快速开始",
        quickstartStep1: "克隆存储库",
        quickstartStep2: "上传到Web服务器",
        quickstartStep3: "运行安装向导",
        quickstartStep4: "配置论坛设置",
        quickstartStep5: "开始构建社区！",
        adminTitle: "⚙️ 管理功能",
        adminFeature1: "用户管理",
        adminFeature2: "论坛配置",
        adminFeature3: "OAuth设置",
        adminFeature4: "积分系统",
        licenseTitle: "📜 许可证",
        licenseDescription: "MicroBoard是根据MIT许可证授权的开源软件。",
        copyright: "© 2025 MicroBoard Team. 版权所有。",
        footerTagline: "数字空间之旅",
        footerMadeWith: "由MicroBoard Team用❤️制作 | 版本 1.0.0",
        github: "🚀 GitHub",
        features: "⭐ 功能",
        docs: "📖 文档",
        download: "⬇️ 下载"
    }
};

// 브라우저 언어 감지
function detectBrowserLanguage() {
    const browserLang = navigator.language || navigator.userLanguage;
    const langCode = browserLang.split('-')[0];
    
    // 지원하는 언어인지 확인
    if (translations[langCode]) {
        return langCode;
    }
    
    // 기본값은 영어
    return 'en';
}

// 언어 변경 함수
function changeLanguage(lang) {
    // 로컬 스토리지에 저장
    localStorage.setItem('preferredLanguage', lang);
    
    // 페이지 리다이렉트
    if (lang === 'en') {
        window.location.href = 'index.html';
    } else {
        window.location.href = `index-${lang}.html`;
    }
}

// 페이지 로드 시 언어 설정
function initLanguage() {
    // 저장된 언어 선호도 확인
    const savedLang = localStorage.getItem('preferredLanguage');
    
    if (savedLang) {
        return savedLang;
    }
    
    // 브라우저 언어 감지
    return detectBrowserLanguage();
}

// 언어 선택기 초기화
function initLanguageSelector(currentLang) {
    const selector = document.getElementById('language-selector');
    if (selector) {
        selector.value = currentLang;
        selector.addEventListener('change', (e) => {
            changeLanguage(e.target.value);
        });
    }
}

// 페이지 로드 시 자동 리다이렉트
window.addEventListener('DOMContentLoaded', () => {
    const currentPage = window.location.pathname.split('/').pop();
    
    // index.html인 경우에만 자동 리다이렉트
    if (currentPage === 'index.html' || currentPage === '') {
        const preferredLang = initLanguage();
        
        // 영어가 아니고 아직 리다이렉트되지 않은 경우
        if (preferredLang !== 'en' && !sessionStorage.getItem('languageRedirected')) {
            sessionStorage.setItem('languageRedirected', 'true');
            changeLanguage(preferredLang);
        }
    }
    
    // 현재 언어 감지 및 선택기 초기화
    let currentLang = 'en';
    if (currentPage.includes('index-ko')) currentLang = 'ko';
    else if (currentPage.includes('index-ja')) currentLang = 'ja';
    else if (currentPage.includes('index-zh')) currentLang = 'zh';
    
    initLanguageSelector(currentLang);
});
