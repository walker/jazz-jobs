## Jazz (formerly "Resumator")

A Wordpress plugin that allows you to have a more featureful Jazz (formerly "Resumator") experience embedded within your site.

You must include jQuery Form in your page:

    http://jquery.malsup.com/form/#download

Use the following javascript to submit the form found on the auto-generated submit-resume page:

    $(document).ready(function() {
      var options = {
          target: '#output1',
          beforeSubmit: doValidation() {

          },
          success: function() {

          },
          dataType:  'json',
      };

      // bind form using 'ajaxForm'
      $('form#resume-submission').ajaxForm(options);
    });

### Customization

Jazz Jobs will search your active theme's directory (not subfolders) looking for these files:

	jazz-jobs-page.php
	jazz-page.php
	jazz-apply-page.php

When found in the theme directory, they are used to customize the:

1. Job Listing Page
2. Job Detail Page
3. Job Application/Form Page

The job listing page might start by looking like this:

```php
<?php get_header(); ?>
<section id="content" role="main">
    <article class="resumator <?php echo $resumator->dom_class; ?>" id="resumator-<?php echo $resumator->dom_id; ?>">
        <div class="wrapper">
            <div class="grid">
                <div class="col-10-12 center clearfix">
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
                </div>
            </div>
        </div>
    </article>
</section>

<?php get_footer(); ?>
```

The job detail page might start by looking like this:

```php
<?php get_header(); ?>
<section id="content" role="main">
    <article class="resumator <?php echo $resumator->dom_class; ?>" id="resumator-<?php echo $resumator->dom_id; ?>">
        <div class="wrapper">
            <div class="grid">
                <div class="col-10-12 center clearfix">
                    <header>
                        <h2><?php echo $resumator->job->title ?></h2>
                    </header>
                    <?php echo $resumator->job->description; ?>

                    <p><a href="<?php echo $resumator->job->apply_now; ?>">Apply Now</a></p>
                </div>
            </div>
        </div>
    </article>
</section>

<?php get_footer(); ?>
```

The job application/form page might start by looking like this:

```php
<?php get_header(); ?>
<section id="content" role="main">
    <article class="resumator <?php echo $resumator->dom_class; ?>" id="resumator-<?php echo $resumator->dom_id; ?>">
        <div class="wrapper">
            <div class="grid">
                <div class="col-10-12 center clearfix">
                    <header>
                        <h2>Apply for <span><?php echo $resumator->job->title; ?></span></h2>
                    </header>

                    <?php echo $resumator->user_form; ?>
                </div>
            </div>
        </div>
    </article>
</section>

<?php get_footer(); ?>
```