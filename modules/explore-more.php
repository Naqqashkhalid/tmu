<?php
function explore_more(){
    $options = get_options(["tmu_movies","tmu_tv_series", "tmu_dramas"]);
    $site_url = get_site_url();
    ?>
    <div class="grid--expbox--v2">
        
        <?php if($options['tmu_movies'] === 'on') { ?>
        <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/movie/" title="Top Movies" alt="Top Movies">
                <h4 class="exp--v2--fonts">Top Movies </h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div>
        <?php } ?>
        <!-- <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/upcoming-movies/" title="Upcoming Movies" alt="Upcoming Movies">
                <h4 class="exp--v2--fonts">Upcoming Movies</h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div> -->
        <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/people/" title="Celebrities" alt="Celebrities">
                <h4 class="exp--v2--fonts">Celebrities </h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div>
        <!-- <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/top-celebrities/" title="Top Celebrities" alt="Top Celebrities">
                <h4 class="exp--v2--fonts">Top Celebrities</h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div> -->
        <?php if($options['tmu_dramas'] === 'on') { ?>
        <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/drama/" title="Top Dramas" alt="Top Dramas">
                <h4 class="exp--v2--fonts">Top Dramas</h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div>

        <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/schedule/" title="Drama Schedule" alt="Drama Schedule">
                <h4 class="exp--v2--fonts">Drama Schedule</h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div>
        <?php } ?>
        <?php if($options['tmu_tv_series'] === 'on') { ?>
        <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/tv/" title="Top Web-Series" alt="Top Web-Series">
                <h4 class="exp--v2--fonts">Top TV-Series</h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div>
        <?php } ?>
        <!-- <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/blog/" title="All Topics" alt="All Topics">
                <h4 class="exp--v2--fonts"> All Topics</h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div> -->
        <div class="flex--exp--boxv2">
            <a href="<?= $site_url ?>/video/" title="All Topics" alt="All Topics">
                <h4 class="exp--v2--fonts">All Videos</h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>
            </a>
        </div>
    </div>
<?php
}