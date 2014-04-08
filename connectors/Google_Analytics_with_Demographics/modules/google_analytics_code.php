<?php
  if (defined('ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_STATUS') and 
      (ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_STATUS == 1)
     )
  {
      $use_demographics = (defined('ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_DEMOGRAPHICS') and (ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_DEMOGRAPHICS == 1));
      if (!preg_match('/^UA-[A-Z0-9\-]+/i',ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_ACCOUNT)) {
        echo "\n<!-- GA Account incorrectly specified : ".ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_ACCOUNT." -->\n";
      } else
      {
        if (ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_DEMOGRAPHICS == 1)  {
          // https://support.google.com/analytics/answer/2444872
?>

<!-- Graith Google Analytics code added -->
<!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//stats.g.doubleclick.net/dc.js','ga');

ga('create', '<?php echo ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_ACCOUNT ?>', 'auto');
ga('send', 'pageview');

</script>
<!-- End Google Analytics -->
<!-- EOF Graith Google Analytics code added -->

<?php
        } else { // GA without Demographics
          // https://developers.google.com/analytics/devguides/collection/analyticsjs/
?>


<!-- Graith Google Analytics code added -->
<!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '<?php echo ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_ACCOUNT ?>', 'auto');
ga('send', 'pageview');

</script>
<!-- End Google Analytics -->
<!-- EOF Graith Google Analytics code added -->

<?php        
        } 
        
    }
  }
?>