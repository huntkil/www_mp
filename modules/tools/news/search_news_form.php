<?php
session_start();
$page_title = "News Search";
include "../../../system/includes/header.php";
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold">News Search</h1>
                <p class="text-sm text-muted-foreground">Search for the latest news articles from around the world</p>
            </div>
        </div>

        <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
            <div class="p-6 space-y-6">
                <form action="search_news.php" method="post" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="query" class="text-sm font-medium">Search Query</label>
                            <input type="text" id="query" name="query" required
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                   placeholder="Enter keywords (e.g., technology, sports, politics)">
                        </div>

                        <div class="space-y-2">
                            <label for="country" class="text-sm font-medium">Country</label>
                            <select id="country" name="country"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="" selected>All Countries</option>
                                <option value="us">🇺🇸 United States</option>
                                <option value="uk">🇬🇧 United Kingdom</option>
                                <option value="ca">🇨🇦 Canada</option>
                                <option value="au">🇦🇺 Australia</option>
                                <option value="de">🇩🇪 Germany</option>
                                <option value="fr">🇫🇷 France</option>
                                <option value="jp">🇯🇵 Japan</option>
                                <option value="kr">🇰🇷 South Korea</option>
                                <option value="cn">🇨🇳 China</option>
                                <option value="in">🇮🇳 India</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="category" class="text-sm font-medium">Category</label>
                            <select id="category" name="category"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">All Categories</option>
                                <option value="general">📰 General</option>
                                <option value="business">💼 Business</option>
                                <option value="entertainment">🎬 Entertainment</option>
                                <option value="health">🏥 Health</option>
                                <option value="science">🔬 Science</option>
                                <option value="sports">⚽ Sports</option>
                                <option value="technology">💻 Technology</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="sortBy" class="text-sm font-medium">Sort By</label>
                            <select id="sortBy" name="sortBy"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="publishedAt">📅 Latest</option>
                                <option value="relevancy">🎯 Relevancy</option>
                                <option value="popularity">🔥 Popularity</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" name="action" value="검색"
                                class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            🔍 Search News
                        </button>
                        <button type="submit" name="action" value="전체 기사"
                                class="inline-flex items-center justify-center rounded-md border border-input bg-background px-6 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            📋 All Articles
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-muted/50 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-3">💡 Search Tips</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <h3 class="font-medium mb-2">Search Examples:</h3>
                    <ul class="space-y-1 text-muted-foreground">
                        <li>• "artificial intelligence"</li>
                        <li>• "climate change"</li>
                        <li>• "cryptocurrency bitcoin"</li>
                        <li>• "space exploration"</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-medium mb-2">Features:</h3>
                    <ul class="space-y-1 text-muted-foreground">
                        <li>• Real-time news from 80+ sources</li>
                        <li>• Filter by country and category</li>
                        <li>• Sort by latest, relevancy, or popularity</li>
                        <li>• Click-through to original articles</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../../../system/includes/footer.php"; ?> 