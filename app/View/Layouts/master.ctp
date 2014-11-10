<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="vi">
<!--<![endif]-->

    <head>
        <?php echo $this->Html->charset(); ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <?php echo $this->Html->meta('icon'/*, 'favicon.gif', array('type' => 'image/gif')*/); ?>

        <title><?php echo $title_for_layout; ?> - ABAM App</title>
        <meta name="description" content="<?php echo @$description_for_layout; ?>">
        <?php echo $this->fetch('meta'); ?>
        <meta name="viewport" content="initial-scale=1,maximum-scale=1">
        <meta name="mobile-web-app-capable" content="yes">

        <script type="text/javascript">
            BASE  = '<?php echo Router::url('/', TRUE); ?>';
            FB_ID = '';
        </script>

        <?php
            echo $this->Html->css('vendor/bootstrap.min');
            echo $this->Html->css('vendor/font-awesome.min');
            echo $this->Html->css('main');

            echo $this->fetch('css');

            echo $this->Html->script('vendor/modernizr-2.6.2.min');
            echo $this->Html->script('vendor/jquery-1.11.1.min');
        ?>
    </head>

    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <div id="fb-root"></div>

        <div class="page-header" id="Top">
            <div class="container">
                <div class="row">
                    <h1 class="heading-title col-xs-12">
                        <?php echo $this->Html->link(
                            'Homepage',
                            '/',
                            array('escape' => FALSE, 'title' => 'Homepage')
                        ); ?>
                    </h1>
                </div>
            </div>
        </div>

        <div class="container">
            <?php echo $this->Session->flash(); ?>

            <?php echo $this->fetch('content'); ?>
        </div>

        <footer class="container" id="Bottom">
            <div class="row">
                <p class="col-xs-12">
                    &copy;
                    <?php echo $this->Html->link('○○○会社', '#', array('target' => '_blank')); ?>
                    <?php echo date('Y'); ?>
                </p>
            </div>
        </footer>

        <!-- Start Go to top -->
        <?php echo $this->Html->link(
            '<b class="fa fa-chevron-circle-up"></b>',
            '#Top',
            array('id' => 'gotoTop', 'class' => '', 'escape' => false)
        ); ?>
        <!-- End Go to top -->


        <!-- Start Page modals -->
        <?php echo $this->fetch('modals'); ?>
        <!-- End Page modals -->


        <!-- Start Scripts -->
        <?php
            echo $this->Html->script('vendor/bootstrap.min');
            echo $this->Html->script('social');
            echo $this->Html->script('plugins');
            echo $this->Html->script('main');

            echo $this->fetch('script');
        ?>

        <?php echo $this->Element('Scripts/ga'); ?>
        <!-- End Scripts -->
    </body>
</html>