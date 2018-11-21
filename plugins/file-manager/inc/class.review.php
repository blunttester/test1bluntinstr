<?php
// Security Check
defined('ABSPATH') || die();

if(!class_exists('FMReviewClass')):
class FMReviewClass{

    // 0. Not asked yet
    // 1. Asked and clicked review
    // 2. Ask me later
    // 3. Not interested to review.

    public $status = array(
        'not-asked-yet',
        'review-successfull',
        'remind-me-later',
        'not-interested'
    );

    function __construct(){
        // Checking the review status
        if(isset($_GET['fm-review-status']) && !empty($_GET['fm-review-status'])){
            $review_status = $_GET['fm-review-status'];
            switch($review_status){
                case 'review-successfull':
                    $this->set_status('review-successfull', time());
                    ?><script>window.location = "https://wordpress.org/support/plugin/file-manager/reviews/#new-post";</script><?php
                    break;
                case 'remind-me-later':
                    $this->set_status('remind-me-later', time());
                    break;
                case 'not-interested':
                    $this->set_status('not-interested', time());
                    break;
                default:
                    break;
            }
        }
    }

    // Get the current status of the review.
    function get_status(){
        // Check if any update is saved
        return unserialize(
            get_option(
                'fm-review-data',
                serialize(
                    array('not-asked-yet', time())
                )
            )
        );
    }

    function set_status($status, $time){
        update_option(
            'fm-review-data',
            serialize(
                array($status, $time)
            )
        );
    }

    // Trenders the block.
    function render(){
        $status = $this->get_status();
        if($status[0] == 'not-interested') return;
        if($status[0] == 'remind-me-later') if( time() < ( (int)$status[1] + ( 7 * 24 * 60 * 60 ) ) ) return;
        if( $status[0] == 'review-successfull' ) return;

    ?>
        <div class="gb-fm-row review-block">
            <div class="message">
                Hi, if you like our plugin you can post a review.
            </div>
            <div class="actions">
                <a target="_blank" href="admin.php?page=file-manager&fm-review-status=review-successfull" class="btn btn-review" title="Leave us a review.">I like your plugin!</a>
                <a href="admin.php?page=file-manager&fm-review-status=remind-me-later" class="btn" title="Remind me later.">I don't have time right now.</a>
                <a href="admin.php?page=file-manager&fm-review-status=not-interested" class="btn btn-not-interested" title="Don't ask again.">I don't care!</a>
            </div>
		</div>
    <?php }

}
endif;
