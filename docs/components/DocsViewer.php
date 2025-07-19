<?php
class DocsViewer {
    private $docsUrl;
    private $title;

    public function __construct($docsUrl = null, $title = 'Junho Docs') {
        // Google Docs URL에 표시 옵션 추가
        $baseUrl = $docsUrl ?? 'https://docs.google.com/document/d/e/2PACX-1vR3LQhLYsNFyfLRa_vpmwPvJP2tl24NJrYhoeQ-DRNXVCsNgrrsRfUEWpK5fuoPOzqHbCq1fhWBKx1g/pub?embedded=true';
        $this->docsUrl = $baseUrl . '&widget=true&chrome=false&rm=minimal&output=html&scrolling=yes';
        $this->title = $title;
    }

    public function render() {
        // Include header
        include_once '../includes/header.php';
        
        // Main content
        $html = '<main class="docs-container">';
        $html .= '<div class="responsive-iframe-container">';
        $html .= '<iframe src="' . htmlspecialchars($this->docsUrl) . '" frameborder="0" allowfullscreen style="width: 100%; height: 100vh; min-height: 100vh;"></iframe>';
        $html .= '</div>';
        $html .= '</main>';

        echo $html;

        // Include footer
        include_once '../includes/footer.php';
    }
} 