# Propose Draft Date

[![Build Status](https://travis-ci.com/humanmade/propose-draft-date.svg?branch=master)](https://travis-ci.com/humanmade/propose-draft-date)

This is a WordPress plugin that provides an interface within the [block editor](https://developer.wordpress.org/blogk-editor) for contributing authors (and other roles without the ability to schedule or publish posts) to select a proposed date for the post. That proposed date will be shown in previews of the post by filtering the "floating" post date, and will take effect when the post is published.

## Filters

The following filters are available to modify the behavior of this plugin.

### `proposed_date.supported_post_types` (PHP filter)

Server-side PHP filter to modify which post types can take a proposed date.

Example: Add proposed date support for a custom post type, and remove it from core pages.

```php
/**
 * Customize the post types which support the proposed date feature.
 *
 * @param array $post_types Array of supported post types.
 *                          Defaults to "post" and "page".
 *
 * @return array Filtered post types array.
 */
function filter_types_with_proposed_dates( array $post_types ) : array {
    $post_types = array_diff( $post_types, [ 'page' ] );
    $post_types[] = 'my_custom_post_type';
    return $post_types;
}
add_filter( 'proposed_date.supported_post_types', 'filter_types_with_proposed_dates', 10, 1 );
```

### `proposed_date.should_accept_proposal` (PHP filter)

Server-side PHP filter to determine, based on the old and new post status and the post object transitioning between those statuses, whether to check for a proposed date and apply it if found.

Example: Accept proposed dates when transitioning to a custom status.

```php
/**
 * Always accept any available proposed date when transitioning to the
 * `my-scheduled` custom post status.
 *
 * @param bool    $accept_proposal Whether a proposal should be accepted.
 * @param string  $new_status      New post status.
 * @param string  $old_status      Previous post status.
 * @param WP_Post $post            The post being updated, before changes are applied.
 *
 * @return bool Whether a proposed date should be applied at this time.
 */
function accept_date_proposals_in_custom_status(
    bool $accept_proposal,
    string $new_status,
    string $old_status,
    WP_Post $post
) : bool {
    if ( $new_status === 'my_custom_status' ) {
        return true;
    }
    return $accept_proposal;
}
add_filter( 'proposed_date.should_accept_proposal', 'accept_date_proposals_in_custom_status', 10, 4 );
```

Example: Always accept proposed dates when present, regardless of other circumstances. (If you do this you should also use the JS-side `proposed_date.date_label` filter to ensure you show the correct date value to contributors.)

```php
add_filter( 'proposed_date.should_accept_proposal', '__return_true' );
```

### `proposed_date.date_label` (JS filter)

Frontend JS filter to determine what date value to show when rendering the proposed date within Document sidebar.

Example: Always show the proposed date, when present.

```js
function overrideProposedDateLabel( label, proposedDate, date ) {
    if ( proposedDate ) {
        return proposedDate;
    }
    return label;
}
wp.hooks.addFilter( 'proposed_date.date_label', 'my-plugin', overrideProposedDateLabel );
```

### `proposed_date.is_floating` (JS filter)

Override whether to consider the post date is considered to be "floating" (that is to say, if the post is meant to be published "Immediately").

Example: Force "floating" date status so that a proposed date will be displayed on the frontend even if an actual scheduled date value is present.

```js
function forceIsFloating( isFloating ) {
    return true;
}
wp.hooks.addFilter( 'proposed_date.is_floating', 'my-plugin', forceIsFloating );
```

### `proposed_date.supported_statuses` (JS filter)

Customize the list of post statuses which support a proposed date value.

```js
function filterSupportedStatuses( statuses ) {
    return [ ...statuses, 'my_custom_status' ];
}
wp.hooks.addFilter( 'proposed_date.supported_statuses', 'my-plugin', filterSupportedStatuses );
```

## Release Process

Given a clean `main` branch with an updated version number in [`plugin.php`](./plugin.php), follow these steps to build and tag a release:

```sh
git checkout release
git merge main
npm run build
git add -f build
git commit -m 'Tag v{ YOUR VERSION NUMBER HERE }'
git tag v{ YOUR VERSION NUMBER HERE }
git push origin release
git push origin tags
```
