## Jazz

A Wordpress plugin that allows you to have a more featureful Jazz experience embedded within your site.

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
