<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds recent twitter posts widget
 */
class grooni_twitter_widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 * Sets up the widgets name, html-class, description
	 */
	function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_scripts' ) );

		// Load the textdomain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		parent::__construct(
			'grooni_twitter_widget',
			__( 'Grooni Twitter Feeds widget', 'grooni-tw' ),
			array(
				'classname'   => 'grooni_twitter_widget',
				'description' => __( 'It shows the last tweets of user from the Twitter', 'grooni-tw' ),
			)
		);
	}

	/**
	 * Loads the plugin's translated strings.
	 *
	 * @since 1.0.4
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'grooni-tw', false, dirname( plugin_basename( __FILE__ ) ) . '//' );
	}

	function gr_tw_default_params() {
		$data = array(
			'title'             => __( 'Latest Tweets', 'grooni-tw' ),
			'userName'          => '',
			'tweetsCount'       => 3,
			'cacheTime'         => 8,
			'userAvatar'        => true,
			'excludeReplies'    => true,
			'consumerKey'       => '',
			'consumerSecret'    => '',
			'accessToken'       => '',
			'accessTokenSecret' => '',
			'dataLang'          => 'en',
		);

		return $data;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return null
	 */
	function form( $instance ) {
		$defaults          = $this->gr_tw_default_params();
		$instance          = wp_parse_args( (array) $instance, $defaults );
		$title             = $instance['title'];
		$userName          = $instance['userName'];
		$tweetsCount       = $instance['tweetsCount'];
		$cacheTime         = $instance['cacheTime'];
		$userAvatar        = $instance['userAvatar'];
		$excludeReplies    = $instance['excludeReplies'];
		$consumerSecret    = trim( $instance['consumerSecret'] );
		$consumerKey       = trim( $instance['consumerKey'] );
		$accessToken       = trim( $instance['accessToken'] );
		$accessTokenSecret = trim( $instance['accessTokenSecret'] );
		if ( ! in_array( 'curl', get_loaded_extensions() ) ) {
			echo '<div class="gr_widget_error">' . __( 'cURL lib is not installed!', 'grooni-tw' ) . '</div>';
		}
		include dirname( __FILE__ ) . '/template/widget_config.php';
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']             = strip_tags( $new_instance['title'] );
		$instance['userName']          = strip_tags( $new_instance['userName'] );
		$instance['tweetsCount']       = $new_instance['tweetsCount'];
		$instance['cacheTime']         = $new_instance['cacheTime'];
		$instance['userAvatar']        = $new_instance['userAvatar'];
		$instance['excludeReplies']    = $new_instance['excludeReplies'];
		$instance['consumerKey']       = trim( $new_instance['consumerKey'] );
		$instance['consumerSecret']    = trim( $new_instance['consumerSecret'] );
		$instance['accessToken']       = trim( $new_instance['accessToken'] );
		$instance['accessTokenSecret'] = trim( $new_instance['accessTokenSecret'] );

		// clear this tweets WP transient
		$transName = 'grooni-ls-tweets-' . $instance['userName'];
		delete_transient( $transName );

		return $instance;
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 *
	 */
	function widget( $args, $instance ) {

		echo $args['before_widget'];

		$gr_tw_title             = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
		$gr_tw_userName          = $instance['userName'];
		$gr_tw_tweetsCount       = $instance['tweetsCount'];
		$gr_tw_cacheTime         = $instance['cacheTime'];
		$gr_tw_userAvatar        = isset( $instance['userAvatar'] ) ? $instance['userAvatar'] : false;
		$gr_tw_excludeReplies    = isset( $instance['excludeReplies'] ) ? $instance['excludeReplies'] : false;
		$gr_tw_consumerSecret    = trim( $instance['consumerSecret'] );
		$gr_tw_consumerKey       = trim( $instance['consumerKey'] );
		$gr_tw_accessTokenSecret = trim( $instance['accessTokenSecret'] );
		$gr_tw_accessToken       = trim( $instance['accessToken'] );

		if ( ! empty( $gr_tw_title ) && trim( $gr_tw_title ) ) {
			echo $args['before_title'] . $gr_tw_title . $args['after_title'];
		}
		if ( $gr_tw_consumerKey == '' || $gr_tw_consumerSecret == '' || $gr_tw_accessTokenSecret == '' || $gr_tw_accessToken == '' ) {
			echo '<div class="gr_widget_error">' . __( 'Bad Authentication data.<br/>Please enter valid API Keys.', 'grooni-tw' ) . '</div>';
		} else {
			?>

			<ul class="gr_tw_tweets">
				<?php
				$tweetsCount       = $gr_tw_tweetsCount;
				$userName          = $gr_tw_userName;
				$cacheTime         = $gr_tw_cacheTime;
				$excludeReplies    = $gr_tw_excludeReplies;
				$userAvatar        = $gr_tw_userAvatar;
				$consumerKey       = trim( $gr_tw_consumerKey );
				$consumerSecret    = trim( $gr_tw_consumerSecret );
				$accessToken       = trim( $gr_tw_accessToken );
				$accessTokenSecret = trim( $gr_tw_accessTokenSecret );

				$transName  = 'grooni-ls-tweets-' . $userName;
				$backupName = $transName . '-bkp';

				if ( false === ( $tweets = get_transient( $transName ) ) ) {
					$api_call   = new Abraham\TwitterOAuth\TwitterOAuth(
						$consumerKey,
						$consumerSecret,
						$accessToken,
						$accessTokenSecret
					);
					$fetchCount = ( $excludeReplies ) ? max( 50, $tweetsCount * 3 ) : $tweetsCount;

					$fetchedTweets = $api_call->get(
						'statuses/user_timeline',
						array(
							'screen_name'     => $userName,
							'count'           => $fetchCount,
							'exclude_replies' => $excludeReplies
						)
					);

					if ( $api_call->getLastHttpCode() == 401 ) {
						echo '<li><div class="gr_widget_error">' . __( 'Bad Authentication data.<br/>Please enter valid API Keys.', 'grooni-tw' ) . '</div></li>';
						$tweets = 0;
					} elseif ( $api_call->getLastHttpCode() != 200 ) {
						$tweets = get_option( $backupName );
					} else {
						$limitToDisplay = min( $tweetsCount, count( $fetchedTweets ) );

						foreach ( $fetchedTweets as $tweet ) {
							$tweet_id    = $tweet->id_str;
							$screen_name = $tweet->user->screen_name;
							$permalink   = 'https://twitter.com/' . $userName . '/status/' . $tweet->id_str;
							$image       = $tweet->user->profile_image_url;
							$text        = $this->gr_tw_sanitize_links( $tweet );
							$time        = date_parse( $tweet->created_at );
							$uTime       = mktime( $time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year'] );

							$tweets[] = array(
								'tweet_id'    => $tweet_id,
								'text'        => $text,
								'screen_name' => $screen_name,
								//'favourite_count' => $tweet->favorite_count,
								//'retweet_count'   => $tweet->retweet_count,
								'name'        => $userName,
								'permalink'   => $permalink,
								'image'       => $image,
								'time'        => $uTime,
							);

							if ( count( $tweets ) >= $limitToDisplay ) {
								break;
							}
						}

						set_transient( $transName, $tweets, 60 * $cacheTime );
						update_option( $backupName, $tweets );

					}
				}

				if ( $tweets ) : ?>
					<?php foreach ( $tweets as $oneTweet ) : ?>
						<li class="gr_tw-tweet">
							<div class="gr_tw-tweet_wrap">
								<?php if ( $userAvatar ) : ?>
									<div class="gr_tw-user_avatar ltr">
										<?php
										echo '<img width="45" height="45"';
										echo ' src="' . str_replace( 'http://', '//', $oneTweet['image'] ) . '" alt="Avatar"/>';
										?>
									</div>
								<?php endif; ?>
								<div class="gr_tweet_data">
									<?php echo $oneTweet['text']; ?>
								</div>

								<div class="gr_tw-times">
									<em>
										<a href="<?php echo $oneTweet['permalink']; ?>" target="_blank" title="<?php echo __( 'Follow', 'grooni-tw' ) . ' ' . $userName . __( 'on Twitter [Opens new window]', 'grooni-tw' ); ?>">
											<?php
											echo $timeDisplay = human_time_diff( $oneTweet['time'], current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'grooni-tw' ); ?>
										</a>
									</em>
								</div>
							</div>
						</li>
					<?php endforeach; ?>

				<?php elseif ( $tweets !== 0 ) : ?>
					<li><?php echo __( 'Waiting for twitter.com... Try reloading the page again', 'grooni-tw' ); ?></li>
				<?php endif; ?>
			</ul>
			<?php
		}

		echo $args['after_widget'];

	}


	/**
	 * Sanitize twitter links
	 *
	 * @param $tweet
	 *
	 * @return mixed|string
	 */
	function gr_tw_sanitize_links( $tweet ) {
		if ( isset( $tweet->retweeted_status ) ) {
			$rt_section = current( explode( ":", $tweet->text ) );
			$text       = $rt_section . ": ";
			$text .= $tweet->retweeted_status->text;
		} else {
			$text = $tweet->text;
		}
		$text = preg_replace( '/((http)+(s)?:\/\/[^<>\s]+)/i', '<a href="$0" target="_blank" rel="nofollow">$0</a>', $text );
		$text = preg_replace( '/[@]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/$1" target="_blank" rel="nofollow">@$1</a>', $text );
		$text = preg_replace( '/[#]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/search?q=%23$1" target="_blank" rel="nofollow">$0</a>', $text );

		return $text;

	}


	function include_admin_scripts( $hook_suffix ) {
		wp_enqueue_script( 'gr_twitter-admin-script',
			plugins_url( 'assets/js/twitter-user-validate.js', dirname( __FILE__ ) ),
			array( 'jquery' ), '161122', true );
	}


}

