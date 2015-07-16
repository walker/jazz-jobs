<?php get_header(); ?>

<section id="content" role="main">
    <article class="resumator <?php echo $resumator->dom_class; ?>" id="resumator-<?php echo $resumator->dom_id; ?>">
        <div class="wrapper">
            <div class="grid">
                <div class="col-10-12 center clearfix">
                    <?php if(isset($resumator->apply_job)) { ?>
                        <header>
                            <h2>Apply for <span><?php echo $resumator->job->title; ?></span></h2>
                        </header>

                        <?php echo $resumator->user_form; ?>
                    <?php } else if(isset($resumator->job)) { ?>
                        <header>
                            <h2><?php echo $resumator->job->title ?></h2>
                        </header>
                        <?php echo $resumator->job->description; ?>

                        <p><a href="<?php echo $resumator->job->apply_now; ?>">Apply Now</a></p>
                    <?php } else if(isset($resumator->jobs)) { ?>
                        <header>
                            <h2>Jobs</h2>
                        </header>
                        <dl>
                            <?php foreach($resumator->jobs as $job) { ?>
                                <dt><?php echo $job->title; ?></dt>
                                <dd>
                                    <p><a href="<?php echo $job->view; ?>">View</a> / <a href="<?php echo $job->apply_now; ?>">Apply Now</a></p>
                                </dd>
                            <?php } ?>
                        </dl>
                    <?php } else if(isset($resumator->applicant)) { ?>

                    <?php } ?>
                </div>
            </div>
        </div>
    </article>
</section>

<?php // get_sidebar(); ?>

<?php get_footer(); ?>
