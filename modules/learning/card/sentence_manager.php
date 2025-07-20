<?php
session_start();

// 언어 파라미터 처리
$language = $_GET['lang'] ?? 'ko';
$isEnglish = $language === 'en';

$pageTitle = $isEnglish ? "English Sentence Management" : "한국어 문장 관리";
require "../../../system/includes/header.php";
require_once 'components/SentenceManager.php';

try {
    $manager = new SentenceManager($language);
    $statistics = $manager->getStatistics();
    $categories = $manager->getCategories();
    $difficulties = $manager->getDifficulties();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold"><?php echo $isEnglish ? 'English Sentence Management' : '한국어 문장 관리'; ?></h1>
                <p class="text-muted-foreground mt-2"><?php echo $isEnglish ? 'Add, edit, and delete sentences.' : '문장을 추가, 수정, 삭제할 수 있습니다.'; ?></p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Language toggle -->
                <?php $otherLang = $isEnglish ? 'ko' : 'en'; ?>
                <?php $otherLangText = $isEnglish ? '한국어' : 'English'; ?>
                <?php $otherLangFile = $isEnglish ? 'wordcard_ko.php' : 'wordcard_en.php'; ?>
                <a href="sentence_manager.php?lang=<?php echo $otherLang; ?>" class="inline-flex items-center gap-2 px-3 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/80 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-languages"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/></svg>
                    <?php echo $otherLangText; ?>
                </a>
                <a href="<?php echo $otherLangFile; ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    <?php echo $isEnglish ? 'View Word Cards' : '단어 카드 보기'; ?>
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="bg-destructive/10 border border-destructive text-destructive px-4 py-3 rounded-lg">
            <strong>오류:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-card border rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-primary"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                    <div>
                        <p class="text-sm text-muted-foreground"><?php echo $isEnglish ? 'Total Sentences' : '총 문장'; ?></p>
                        <p class="text-2xl font-bold"><?php echo $statistics['total'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-card border rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag text-primary"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.002 2.002 0 0 0 2.828 0l7.172-7.172a2 2 0 0 0 0-2.828z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                    <div>
                        <p class="text-sm text-muted-foreground"><?php echo $isEnglish ? 'Categories' : '카테고리'; ?></p>
                        <p class="text-2xl font-bold"><?php echo count($categories); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-card border rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bar-chart text-primary"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>
                    <div>
                        <p class="text-sm text-muted-foreground"><?php echo $isEnglish ? 'Difficulties' : '난이도'; ?></p>
                        <p class="text-2xl font-bold"><?php echo count($difficulties); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-card border rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock text-primary"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                    <div>
                        <p class="text-sm text-muted-foreground"><?php echo $isEnglish ? 'Last Updated' : '마지막 업데이트'; ?></p>
                        <p class="text-sm font-medium"><?php echo date('m/d H:i'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-card border rounded-lg p-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="<?php echo $isEnglish ? 'Search sentences...' : '문장 검색...'; ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex gap-2">
                    <select id="categoryFilter" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value=""><?php echo $isEnglish ? 'All Categories' : '모든 카테고리'; ?></option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="difficultyFilter" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value=""><?php echo $isEnglish ? 'All Difficulties' : '모든 난이도'; ?></option>
                        <?php foreach ($difficulties as $difficulty): ?>
                        <option value="<?php echo htmlspecialchars($difficulty); ?>"><?php echo htmlspecialchars($difficulty); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button id="addSentenceBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                    <?php echo $isEnglish ? 'Add Sentence' : '문장 추가'; ?>
                </button>
                <button id="bulkDeleteBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-destructive text-destructive-foreground rounded-lg hover:bg-destructive/90 transition-colors" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><polyline points="3,6 5,6 21,6"/><path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                    <?php echo $isEnglish ? 'Delete Selected' : '선택 삭제'; ?>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button id="exportBtn" class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg hover:bg-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    <?php echo $isEnglish ? 'Export' : '내보내기'; ?>
                </button>
                <button id="importBtn" class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg hover:bg-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    <?php echo $isEnglish ? 'Import' : '가져오기'; ?>
                </button>
            </div>
        </div>

        <!-- Sentences Table -->
        <div class="bg-card border rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="text-left p-4">
                                <input type="checkbox" id="selectAll" class="rounded">
                            </th>
                            <th class="text-left p-4">ID</th>
                            <th class="text-left p-4"><?php echo $isEnglish ? 'Sentence' : '문장'; ?></th>
                            <th class="text-left p-4"><?php echo $isEnglish ? 'Category' : '카테고리'; ?></th>
                            <th class="text-left p-4"><?php echo $isEnglish ? 'Difficulty' : '난이도'; ?></th>
                            <th class="text-left p-4"><?php echo $isEnglish ? 'Created' : '생성일'; ?></th>
                            <th class="text-left p-4"><?php echo $isEnglish ? 'Updated' : '수정일'; ?></th>
                            <th class="text-left p-4"><?php echo $isEnglish ? 'Actions' : '관리'; ?></th>
                        </tr>
                    </thead>
                    <tbody id="sentencesTableBody">
                        <!-- JavaScript로 동적 로딩 -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="flex items-center justify-between p-4 border-t">
                <div class="text-sm text-muted-foreground">
                    <?php echo $isEnglish ? 'Total' : '총'; ?> <span id="totalCount">0</span><?php echo $isEnglish ? ' sentences' : '개 문장'; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage" class="px-3 py-1 border rounded hover:bg-accent transition-colors" disabled><?php echo $isEnglish ? 'Previous' : '이전'; ?></button>
                    <span id="pageInfo" class="px-3 py-1">1 / 1</span>
                    <button id="nextPage" class="px-3 py-1 border rounded hover:bg-accent transition-colors" disabled><?php echo $isEnglish ? 'Next' : '다음'; ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Sentence Modal -->
<div id="sentenceModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-background border rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 id="modalTitle" class="text-lg font-semibold"><?php echo $isEnglish ? 'Add Sentence' : '문장 추가'; ?></h3>
            <button id="closeModal" class="text-muted-foreground hover:text-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        
        <form id="sentenceForm">
            <input type="hidden" id="sentenceId">
            
            <div class="space-y-4">
                <div>
                    <label for="sentenceText" class="block text-sm font-medium mb-2"><?php echo $isEnglish ? 'Sentence' : '문장'; ?></label>
                    <textarea id="sentenceText" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="<?php echo $isEnglish ? 'Enter sentence...' : '문장을 입력하세요...'; ?>" required></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sentenceCategory" class="block text-sm font-medium mb-2"><?php echo $isEnglish ? 'Category' : '카테고리'; ?></label>
                        <select id="sentenceCategory" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sentenceDifficulty" class="block text-sm font-medium mb-2"><?php echo $isEnglish ? 'Difficulty' : '난이도'; ?></label>
                        <select id="sentenceDifficulty" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <?php foreach ($difficulties as $difficulty): ?>
                            <option value="<?php echo htmlspecialchars($difficulty); ?>"><?php echo htmlspecialchars($difficulty); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-2 mt-6">
                <button type="button" id="cancelBtn" class="px-4 py-2 border rounded-lg hover:bg-accent transition-colors">취소</button>
                <button type="submit" id="saveBtn" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">저장</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-background border rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">데이터 가져오기</h3>
            <button id="closeImportModal" class="text-muted-foreground hover:text-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label for="importFile" class="block text-sm font-medium mb-2">파일 선택</label>
                <input type="file" id="importFile" accept=".json,.csv" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            
            <div class="text-sm text-muted-foreground">
                <p>지원 형식: JSON, CSV</p>
                <p>기존 데이터와 병합됩니다.</p>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-2 mt-6">
            <button type="button" id="cancelImportBtn" class="px-4 py-2 border rounded-lg hover:bg-accent transition-colors">취소</button>
            <button type="button" id="confirmImportBtn" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">가져오기</button>
        </div>
    </div>
</div>

<script>
// 전역 변수
let currentPage = 1;
let totalPages = 1;
let selectedIds = new Set();
let currentFilters = {};
const isEnglish = <?php echo $isEnglish ? 'true' : 'false'; ?>;
const language = '<?php echo $language; ?>';

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    loadSentences();
    setupEventListeners();
});

// 이벤트 리스너 설정
function setupEventListeners() {
    // 검색 및 필터
    document.getElementById('searchInput').addEventListener('input', debounce(loadSentences, 300));
    document.getElementById('categoryFilter').addEventListener('change', loadSentences);
    document.getElementById('difficultyFilter').addEventListener('change', loadSentences);
    
    // 페이지네이션
    document.getElementById('prevPage').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('nextPage').addEventListener('click', () => changePage(currentPage + 1));
    
    // 전체 선택
    document.getElementById('selectAll').addEventListener('change', toggleSelectAll);
    
    // 모달 관련
    document.getElementById('addSentenceBtn').addEventListener('click', () => openModal());
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('sentenceForm').addEventListener('submit', saveSentence);
    
    // 일괄 삭제
    document.getElementById('bulkDeleteBtn').addEventListener('click', bulkDelete);
    
    // 내보내기/가져오기
    document.getElementById('exportBtn').addEventListener('click', exportData);
    document.getElementById('importBtn').addEventListener('click', () => document.getElementById('importModal').style.display = 'flex');
    document.getElementById('closeImportModal').addEventListener('click', () => document.getElementById('importModal').style.display = 'none');
    document.getElementById('cancelImportBtn').addEventListener('click', () => document.getElementById('importModal').style.display = 'none');
    document.getElementById('confirmImportBtn').addEventListener('click', importData);
}

// 문장 목록 로드
async function loadSentences() {
    try {
        // 필터 수집
        currentFilters = {
            search: document.getElementById('searchInput').value,
            category: document.getElementById('categoryFilter').value,
            difficulty: document.getElementById('difficultyFilter').value,
            page: currentPage,
            limit: 20
        };
        
        // API 호출
        const params = new URLSearchParams(currentFilters);
        params.append('lang', language);
        const response = await fetch(`api/get_sentences.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderSentences(data.data.sentences);
            updatePagination(data.data.pagination);
            updateStatistics(data.data.statistics);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError(isEnglish ? 'Error loading sentences.' : '문장 목록을 불러오는 중 오류가 발생했습니다.');
        console.error(error);
    }
}

// 문장 테이블 렌더링
function renderSentences(sentences) {
    const tbody = document.getElementById('sentencesTableBody');
    tbody.innerHTML = '';
    
    sentences.forEach(sentence => {
        const row = document.createElement('tr');
        row.className = 'border-t hover:bg-muted/50';
        
        row.innerHTML = `
            <td class="p-4">
                <input type="checkbox" class="sentence-checkbox rounded" value="${sentence.id}">
            </td>
            <td class="p-4">${sentence.id}</td>
            <td class="p-4">${escapeHtml(sentence.text)}</td>
            <td class="p-4">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                    ${escapeHtml(sentence.category)}
                </span>
            </td>
            <td class="p-4">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-secondary/10 text-secondary-foreground">
                    ${escapeHtml(sentence.difficulty)}
                </span>
            </td>
            <td class="p-4 text-sm text-muted-foreground">${formatDate(sentence.created_at)}</td>
            <td class="p-4 text-sm text-muted-foreground">${formatDate(sentence.updated_at)}</td>
            <td class="p-4">
                <div class="flex items-center gap-2">
                    <button onclick="editSentence(${sentence.id})" class="p-1 text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                    <button onclick="deleteSentence(${sentence.id})" class="p-1 text-muted-foreground hover:text-destructive">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><polyline points="3,6 5,6 21,6"/><path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
    
    // 체크박스 이벤트 리스너 추가
    document.querySelectorAll('.sentence-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDeleteButton);
    });
}

// 페이지네이션 업데이트
function updatePagination(pagination) {
    currentPage = pagination.current_page;
    totalPages = pagination.total_pages;
    
    document.getElementById('totalCount').textContent = pagination.total_count;
    document.getElementById('pageInfo').textContent = `${currentPage} / ${totalPages}`;
    document.getElementById('prevPage').disabled = !pagination.has_prev;
    document.getElementById('nextPage').disabled = !pagination.has_next;
}

// 통계 업데이트
function updateStatistics(statistics) {
    // 통계 카드 업데이트 (필요시)
}

// 페이지 변경
function changePage(page) {
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        loadSentences();
    }
}

// 모달 열기
function openModal(sentence = null) {
    const modal = document.getElementById('sentenceModal');
    const title = document.getElementById('modalTitle');
    const form = document.getElementById('sentenceForm');
    
    if (sentence) {
        // 편집 모드
        title.textContent = isEnglish ? 'Edit Sentence' : '문장 수정';
        document.getElementById('sentenceId').value = sentence.id;
        document.getElementById('sentenceText').value = sentence.text;
        document.getElementById('sentenceCategory').value = sentence.category;
        document.getElementById('sentenceDifficulty').value = sentence.difficulty;
    } else {
        // 추가 모드
        title.textContent = isEnglish ? 'Add Sentence' : '문장 추가';
        form.reset();
        document.getElementById('sentenceId').value = '';
    }
    
    modal.style.display = 'flex';
}

// 모달 닫기
function closeModal() {
    document.getElementById('sentenceModal').style.display = 'none';
}

// 문장 저장
async function saveSentence(event) {
    event.preventDefault();
    
    const id = document.getElementById('sentenceId').value;
    const text = document.getElementById('sentenceText').value.trim();
    const category = document.getElementById('sentenceCategory').value;
    const difficulty = document.getElementById('sentenceDifficulty').value;
    
    if (!text) {
        showError(isEnglish ? 'Please enter a sentence.' : '문장 내용을 입력해주세요.');
        return;
    }
    
    try {
        const url = id ? 'api/update_sentence.php' : 'api/add_sentence.php';
        const method = id ? 'PUT' : 'POST';
        
        const response = await fetch(`${url}?lang=${language}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id || undefined,
                text: text,
                category: category,
                difficulty: difficulty
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal();
            loadSentences();
            showSuccess(data.message);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('문장 저장 중 오류가 발생했습니다.');
        console.error(error);
    }
}

// 문장 편집
async function editSentence(id) {
    try {
        const response = await fetch(`api/get_sentences.php?id=${id}`);
        const data = await response.json();
        
        if (data.success && data.data.sentences.length > 0) {
            openModal(data.data.sentences[0]);
        } else {
            showError('문장을 찾을 수 없습니다.');
        }
    } catch (error) {
        showError('문장 정보를 불러오는 중 오류가 발생했습니다.');
        console.error(error);
    }
}

// 문장 삭제
async function deleteSentence(id) {
    if (!confirm('정말로 이 문장을 삭제하시겠습니까?')) {
        return;
    }
    
    try {
        const response = await fetch(`api/delete_sentence.php?lang=${language}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadSentences();
            showSuccess(data.message);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('문장 삭제 중 오류가 발생했습니다.');
        console.error(error);
    }
}

// 전체 선택 토글
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.sentence-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedIds.add(parseInt(checkbox.value));
        } else {
            selectedIds.delete(parseInt(checkbox.value));
        }
    });
    
    updateBulkDeleteButton();
}

// 일괄 삭제 버튼 업데이트
function updateBulkDeleteButton() {
    const checkboxes = document.querySelectorAll('.sentence-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    selectedIds.clear();
    checkboxes.forEach(checkbox => {
        selectedIds.add(parseInt(checkbox.value));
    });
    
    if (selectedIds.size > 0) {
        bulkDeleteBtn.style.display = 'inline-flex';
        bulkDeleteBtn.textContent = `선택 삭제 (${selectedIds.size})`;
    } else {
        bulkDeleteBtn.style.display = 'none';
    }
}

// 일괄 삭제
async function bulkDelete() {
    if (selectedIds.size === 0) {
        showError('삭제할 문장을 선택해주세요.');
        return;
    }
    
    if (!confirm(`선택한 ${selectedIds.size}개의 문장을 정말로 삭제하시겠습니까?`)) {
        return;
    }
    
    try {
        const response = await fetch(`api/delete_sentence.php?lang=${language}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: Array.from(selectedIds) })
        });
        
        const data = await response.json();
        
        if (data.success) {
            selectedIds.clear();
            document.getElementById('selectAll').checked = false;
            loadSentences();
            showSuccess(data.message);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('일괄 삭제 중 오류가 발생했습니다.');
        console.error(error);
    }
}

// 데이터 내보내기
async function exportData() {
    try {
        const response = await fetch('api/get_sentences.php?limit=1000');
        const data = await response.json();
        
        if (data.success) {
            const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `sentences_ko_${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showSuccess('데이터가 성공적으로 내보내졌습니다.');
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('데이터 내보내기 중 오류가 발생했습니다.');
        console.error(error);
    }
}

// 데이터 가져오기
async function importData() {
    const fileInput = document.getElementById('importFile');
    const file = fileInput.files[0];
    
    if (!file) {
        showError('파일을 선택해주세요.');
        return;
    }
    
    try {
        const text = await file.text();
        const format = file.name.endsWith('.csv') ? 'csv' : 'json';
        
        // 여기서는 간단히 JSON만 처리
        if (format === 'json') {
            const data = JSON.parse(text);
            // 실제로는 import API를 호출해야 함
            showSuccess('파일이 성공적으로 읽혔습니다. (실제 가져오기 기능은 추가 구현 필요)');
        } else {
            showError('CSV 형식은 아직 지원하지 않습니다.');
        }
        
        document.getElementById('importModal').style.display = 'none';
        fileInput.value = '';
    } catch (error) {
        showError('파일 읽기 중 오류가 발생했습니다.');
        console.error(error);
    }
}

// 유틸리티 함수들
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ko-KR');
}

function showSuccess(message) {
    // 간단한 성공 메시지 표시
    alert(message);
}

function showError(message) {
    // 간단한 오류 메시지 표시
    alert('오류: ' + message);
}
</script>

<?php require "../../../system/includes/footer.php"; ?> 