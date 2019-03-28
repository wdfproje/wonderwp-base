<?php
/**
 * The template used for displaying page content
 *
 * @package    WordPress
 * @subpackage WonderWp_theme
 */
?>

<div id="post-<?php the_ID(); ?>" <?php post_class('page-' . $post->post_name); ?> data-name="<?php echo $post->post_name; ?>">
    <?php
    /** @var \WonderWp\Theme\Core\Service\ThemeViewService $themeViewService */
    $themeViewService = wwp_get_theme_service('view');
    $postThumb = $themeViewService->getFeaturedImage(get_the_ID());
    ?>
    <?php if (!empty($postThumb)) : ?>
        <div class="post-thumbnail">
            <?php the_post_thumbnail(); ?>
        </div><!-- .post-thumbnail -->
    <?php endif; ?>

    <header class="entry-header <?php if (!empty($postThumb)) {
        echo 'hasPostThumb';
    } ?>">
        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

    </header><!-- .entry-header -->

    <div class="entry-content">
        <main class="main">

        <?php
        /** @var \WonderWp\Theme\Core\Service\ThemeViewService $viewService */
        if (!empty($viewService) && is_object($viewService)) {
            echo $viewService->getBreadcrumbs();
        } ?>

        <?php the_content(); ?>
        <?php
        wp_link_pages([
            'before'      => '<div class="page-links"><span class="page-links-title">' . __('Pages:', 'twentyfifteen') . '</span>',
            'after'       => '</div>',
            'link_before' => '<span>',
            'link_after'  => '</span>',
            'pagelink'    => '<span class="screen-reader-text">' . __('Page', 'twentyfifteen') . ' </span>%',
            'separator'   => '<span class="screen-reader-text">, </span>',
        ]);
        ?>
        </main><!-- main -->
    </div><!-- .entry-content -->

    <?php edit_post_link(__('Edit', 'twentyfifteen'), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->'); ?>

</div><!-- #post-## -->