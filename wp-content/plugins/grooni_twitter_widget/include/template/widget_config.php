<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title: ', 'grooni-tw' ); ?>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
		       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
		       value="<?php echo esc_attr( $title ); ?>"/></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'userName' ); ?>"><?php _e( 'Twitter User Name: ', 'grooni-tw' ); ?>
		<input class="widefat gr_tw-twitter_username" id="<?php echo $this->get_field_id( 'userName' ); ?>"
		       name="<?php echo $this->get_field_name( 'userName' ); ?>" type="text"
		       value="<?php echo esc_attr( $userName ); ?>"/></label>
	<span class="widefat username-validator"><?php _e( 'Start entering your user name: ', 'grooni-tw' ); ?></span>
</p>
<p>
	<label
		for="<?php echo $this->get_field_id( 'cacheTime' ); ?>"><?php _e( 'Tweets Cache Time (in minutes): ', 'grooni-tw' ); ?>
		<input class="widefat" id="<?php echo $this->get_field_id( 'cacheTime' ); ?>"
		       name="<?php echo $this->get_field_name( 'cacheTime' ); ?>" type="text"
		       value="<?php echo esc_attr( $cacheTime ); ?>"/></label>
</p>
<p>
	<label
		for="<?php echo $this->get_field_id( 'tweetsCount' ); ?>"><?php _e( 'Number of Tweets to show: ', 'grooni-tw' ); ?>
		<input class="widefat" id="<?php echo $this->get_field_id( 'tweetsCount' ); ?>"
		       name="<?php echo $this->get_field_name( 'tweetsCount' ); ?>" type="text"
		       value="<?php echo esc_attr( $tweetsCount ); ?>"/></label>
</p>
<p>
	<input class="checkbox" type="checkbox"
	       value="true" <?php checked( ( isset( $instance['userAvatar'] ) && ( $instance['userAvatar'] == "true" ) ), true ); ?>
	       id="<?php echo $this->get_field_id( 'userAvatar' ); ?>"
	       name="<?php echo $this->get_field_name( 'userAvatar' ); ?>"/>
	<label
		for="<?php echo $this->get_field_id( 'userAvatar' ); ?>"><?php _e( 'Show User Avatar', 'grooni-tw' ); ?></label>
</p>
<p>
	<input class="checkbox"
	       type="checkbox" <?php checked( ( isset( $instance['excludeReplies'] ) && ( $instance['excludeReplies'] == "true" ) ), true ); ?>
	       id="<?php echo $this->get_field_id( 'excludeReplies' ); ?>" value="true"
	       name="<?php echo $this->get_field_name( 'excludeReplies' ); ?>"/>
	<label
		for="<?php echo $this->get_field_id( 'excludeReplies' ); ?>"><?php _e( 'Exclude @replies', 'grooni-tw' ); ?></label>
</p>


<h4><?php _e( 'Twitter API Settings', 'grooni-tw' ); ?></h4>
<div>
	<p>
		<label
			for="<?php echo $this->get_field_id( 'consumerKey' ); ?>"><?php _e( 'API key: ', 'grooni-tw' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'consumerKey' ); ?>"
			       name="<?php echo $this->get_field_name( 'consumerKey' ); ?>" type="text"
			       value="<?php echo esc_attr( $consumerKey ); ?>"/></label>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'consumerSecret' ); ?>"><?php _e( 'API secret: ', 'grooni-tw' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'consumerSecret' ); ?>"
			       name="<?php echo $this->get_field_name( 'consumerSecret' ); ?>" type="password"
			       value="<?php echo esc_attr( $consumerSecret ); ?>"/>
		</label>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'accessToken' ); ?>"><?php _e( 'Access Token: ', 'grooni-tw' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'accessToken' ); ?>"
			       name="<?php echo $this->get_field_name( 'accessToken' ); ?>" type="text"
			       value="<?php echo esc_attr( $accessToken ); ?>"/>
		</label>
	</p>

	<p>
		<label
			for="<?php echo $this->get_field_id( 'accessTokenSecret' ); ?>"><?php _e( 'Access Token Secret: ', 'grooni-tw' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'accessTokenSecret' ); ?>"
			       name="<?php echo $this->get_field_name( 'accessTokenSecret' ); ?>" type="password"
			       value="<?php echo esc_attr( $accessTokenSecret ); ?>"/>
		</label>
	</p>

</div>
