<?php
session_start();
$page_title = "Family Tour";
require "../../../system/includes/header.php";
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold">Family Tour Schedule</h1>
            <p class="text-muted-foreground">4-day tour plan in Gyeongju</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Day 1 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Day 1</h2>
                        <span class="inline-flex items-center rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary ring-1 ring-inset ring-primary/20">
                            Day 1
                        </span>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Schedule</h3>
                            <p class="text-sm">
                                Check-in at Hanwha Resort, Visit 
                                <a href="https://map.naver.com/v5/entry/place/11620556?lng=129.22789585894554&lat=35.82942549226298&placePath=%2Fhome&entry=plt&searchType=place" 
                                   target="_blank" 
                                   class="text-primary hover:underline">Gyeongju National Museum</a>, 
                                Tour Anapji (Donggung Palace and Wolji Pond)
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Address</h3>
                            <p class="text-sm">
                                <a href="https://map.naver.com/v5/entry/place/33243753?lng=129.2701212&lat=35.860738&placePath=%2Fhome&entry=plt&searchType=place" 
                                   target="_blank" 
                                   class="text-primary hover:underline">Hanwha Resort</a>, 
                                182-27 Hanwha Condominium, Bomun-ro, Gyeongju
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Travel Time</h3>
                            <p class="text-sm">Resort location, 10min 5km, 15min 6km</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Estimated Cost</h3>
                            <p class="text-sm">Accommodation - Resort rate, Meals - 40,000 KRW, Admission - 20,000 KRW</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day 2 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Day 2</h2>
                        <span class="inline-flex items-center rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary ring-1 ring-inset ring-primary/20">
                            Day 2
                        </span>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Schedule</h3>
                            <p class="text-sm">
                                Visit 
                                <a href="https://map.naver.com/v5/entry/place/11663971?lng=129.3318695675178&lat=35.79038190833887&placePath=%2Fhome&entry=plt&searchType=place" 
                                   target="_blank" 
                                   class="text-primary hover:underline">Bulguksa Temple</a>, 
                                <a href="https://map.naver.com/v5/entry/place/11663972?lng=129.34922790005&lat=35.7948516999729&placePath=%2Fhome&entry=plt&searchType=place" 
                                   target="_blank" 
                                   class="text-primary hover:underline">Seokguram Grotto</a>, 
                                <a href="https://map.naver.com/v5/entry/place/13007073?lng=129.311036000023&lat=35.8045659999873&placePath=%2Fhome&entry=plt&searchType=place" 
                                   target="_blank" 
                                   class="text-primary hover:underline">Gyeongju Folk Craft Village</a>
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Address</h3>
                            <p class="text-sm">385 Bulguk-ro, 238 Seokgul-ro, 230 Bobul-ro, Gyeongju</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Travel Time</h3>
                            <p class="text-sm">30min 20km, 40min 24km, 20min 12km</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Estimated Cost</h3>
                            <p class="text-sm">Meals - 40,000 KRW, Admission & Experience - 30,000 KRW</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day 3 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Day 3</h2>
                        <span class="inline-flex items-center rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary ring-1 ring-inset ring-primary/20">
                            Day 3
                        </span>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Schedule</h3>
                            <p class="text-sm">Cheomseongdae, Daereungwon Tomb Complex, Hwangnidan-gil, Gyeongju World California Beach</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Address</h3>
                            <p class="text-sm">Inwang-dong, 747 Taegong-ro, 544-31 Bomun-ro, Gyeongju</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Travel Time</h3>
                            <p class="text-sm">15min 6km, 10min 5km, 20min 10km</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Estimated Cost</h3>
                            <p class="text-sm">Meals - 40,000 KRW, Admission - 50,000 KRW</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day 4 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Day 4</h2>
                        <span class="inline-flex items-center rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary ring-1 ring-inset ring-primary/20">
                            Day 4
                        </span>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Schedule</h3>
                            <p class="text-sm">Tohamsan Mountain hiking or cable car, Check-out and return</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Address</h3>
                            <p class="text-sm">409-37 Bungwang-ro, Gyeongju</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Travel Time</h3>
                            <p class="text-sm">25min 15km</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-muted-foreground">Estimated Cost</h3>
                            <p class="text-sm">Meals - 20,000 KRW, Other expenses - 20,000 KRW</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center">
            <a href="gyeongju_map.html" target="_blank" 
               class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                View Full Map
            </a>
        </div>
    </div>
</div>

<?php require "../../../system/includes/footer.php"; ?> 