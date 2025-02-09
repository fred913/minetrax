<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * GENERAL SECTION / NO LOGIN
 * Note: We need forbid-banned-user in no-auth section too so that if user login, it automatically get redirected to banned page
 */
Route::middleware(['forbid-banned-user', 'redirect-uncompleted-user'])->group(function() {
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'home'])->name('home');
    Route::get('news/{news:slug}', [\App\Http\Controllers\NewsController::class, 'show'])->name('news.show');
    Route::get('news', [\App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
    Route::get('post', [\App\Http\Controllers\PostController::class, 'index'])->name('post.index');
    Route::get('post/{post}', [\App\Http\Controllers\PostController::class, 'show'])->name('post.show');
    Route::get('post/user/{user:username}', [\App\Http\Controllers\PostController::class, 'indexForUser'])->name('post.user.index');
    Route::get('post/{post}/comment', [\App\Http\Controllers\PostController::class, 'indexComment'])->name('post.comment.index');
    Route::get('stats', [\App\Http\Controllers\PlayerController::class, 'index'])->name('player.index');
    Route::get('stats/player/{player}', [\App\Http\Controllers\PlayerController::class, 'show'])->name('player.show');
    Route::get('did-you-know', [\App\Http\Controllers\HomeController::class, 'didYouKnow'])->name('didyouknow.get');

    Route::get('server/{server}/ping', [\App\Http\Controllers\ServerController::class, 'pingServer'])->name('server.ping.get');
    Route::get('server/{server}/query', [\App\Http\Controllers\ServerController::class, 'queryServer'])->name('server.query.get');
    Route::get('server/{server}/webquery', [\App\Http\Controllers\ServerController::class, 'queryServerWithWebQueryProtocol'])->name('server.webquery.get');

    Route::get('@{user:username}', [\App\Http\Controllers\UserController::class, 'showProfile'])->name('user.public.get');
    Route::get('/staff-members', [\App\Http\Controllers\UserController::class, 'indexStaff'])->name('staff.index');

    Route::get('pages/{customPage:path}', [\App\Http\Controllers\CustomPageController::class, 'show'])->name('custom-page.show');

    Route::get('search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');

    Route::get('auth/{provider}', [\App\Http\Controllers\SocialAuthController::class,'redirect'])->name('social.login')->middleware('guest');
    Route::get('auth/{provider}/callback', [\App\Http\Controllers\SocialAuthController::class,'handleCallback'])->name('social.login.callback')->middleware('guest');

    Route::get('/features', [\App\Http\Controllers\HomeController::class, 'features'])->name('features.list');
    Route::get('/version-check', [\App\Http\Controllers\HomeController::class, 'version'])->name('version.check');

    Route::get('player/avatar/{uuid}/{username}', [\App\Http\Controllers\PlayerController::class, 'getAvatarImage'])->name('player.avatar.get');
    Route::get('player/skin/{uuid}/{username}', [\App\Http\Controllers\PlayerController::class, 'getSkinImage'])->name('player.skin.get');
    Route::get('player/render/{uuid}/{username}', [\App\Http\Controllers\PlayerController::class, 'getRenderImage'])->name('player.render.get');
});

/**
 * USER SECTION/LOGGED IN
 */
Route::middleware(['auth:sanctum', 'forbid-banned-user', 'redirect-uncompleted-user', 'verified-if-enabled'])->group(function () {
    // Shouts
    Route::get('shout', [\App\Http\Controllers\ShoutController::class, 'index'])->name('shout.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('shout', [\App\Http\Controllers\ShoutController::class, 'store'])->name('shout.store')->middleware('forbid-muted-user');
    Route::delete('shout/{shout}', [\App\Http\Controllers\ShoutController::class, 'destroy'])->name('shout.delete');

    // Posts
    Route::post('post', [\App\Http\Controllers\PostController::class, 'store'])->name('post.store')->middleware('forbid-muted-user');
    Route::delete('post/{post}', [\App\Http\Controllers\PostController::class, 'destroy'])->name('post.delete');
    // Post Comments
    Route::post('post/{post}/comment', [\App\Http\Controllers\PostController::class, 'postComment'])->name('post.comment.store')->middleware('forbid-muted-user');
    Route::delete('post/{post}/comment/{comment}', [\App\Http\Controllers\PostController::class, 'deleteComment'])->name('post.comment.delete');

    // Reactions
    Route::post('reaction/post/{post}/like', [\App\Http\Controllers\PostController::class, 'likePost'])->name('reaction.post.like');
    Route::post('reaction/post/{post}/unlike', [\App\Http\Controllers\PostController::class, 'unlikePost'])->name('reaction.post.unlike');

    // Polls
    Route::get('poll', [\App\Http\Controllers\PollController::class, 'index'])->name('poll.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('poll/{poll}/option/{option}/vote', [\App\Http\Controllers\PollController::class, 'vote'])->name('poll.vote');

    // User
    Route::post('auth/user/post-registration-setup', [\App\Http\Controllers\UserProfileController::class, 'postRegistrationSetup'])->name('auth.post-reg-setup')->withoutMiddleware(['redirect-uncompleted-user', 'verified-if-enabled']);
    Route::delete('auth/user/remove-cover', [\App\Http\Controllers\UserProfileController::class, 'deleteCoverImage'])->name('current-user-cover.destroy');
    Route::put('auth/user/notification-preferences', [\App\Http\Controllers\UserProfileController::class, 'putUpdateNotificationPreference'])->name('auth.put-notification-preferences')->withoutMiddleware('verified-if-enabled');

    // Notifications
    Route::get('user/notification', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notification.index')->withoutMiddleware(['redirect-uncompleted-user', 'verified-if-enabled']);
    Route::post('user/notification/read', [\App\Http\Controllers\NotificationController::class, 'postMarkAsRead'])->name('notification.mark-as-read')->withoutMiddleware('verified-if-enabled');

    // Account Linker
    Route::get('account-link/verify/{uuid}/{server}', [\App\Http\Controllers\AccountLinkController::class, 'verify'])->name('account-link.verify');
    Route::delete('account-link/remove/{player:uuid}', [\App\Http\Controllers\AccountLinkController::class, 'unlink'])->name('account-link.delete')->middleware('password.confirm');
    Route::get('user/linked-players', [\App\Http\Controllers\AccountLinkController::class, 'listMyPlayers'])->name('linked-player.list')->withoutMiddleware(['verified-if-enabled']);

    // Server Chatlog
    Route::get('chatlog/{server}', [\App\Http\Controllers\ServerChatlogController::class, 'index'])->name('chatlog.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('chatlog/{server}', [\App\Http\Controllers\ServerChatlogController::class, 'sendToServer'])->name('chatlog.send')->middleware(['forbid-muted-user', 'throttle:chat']);
});

/**
 * ADMIN SECTION
 */
Route::middleware(['auth:sanctum', 'verified-if-enabled', 'forbid-banned-user', 'staff-member', 'redirect-uncompleted-user'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('user', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('user.index');
    //  Route::get('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('user.show');
    Route::get('user/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('user.edit');
    Route::put('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('user.update');
    Route::post('user/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('user.ban');
    Route::post('user/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('user.unban');
    Route::post('user/{user}/mute', [\App\Http\Controllers\Admin\UserController::class, 'mute'])->name('user.mute');
    Route::post('user/{user}/unmute', [\App\Http\Controllers\Admin\UserController::class, 'unmute'])->name('user.unmute');

    Route::get('impersonate/{user}/take', [\App\Http\Controllers\Admin\ImpersonateController::class, 'take'])->name('impersonate.take')->withoutMiddleware(['auth:sanctum']);
    Route::get('impersonate/leave', [\App\Http\Controllers\Admin\ImpersonateController::class, 'leave'])->name('impersonate.leave')->withoutMiddleware('auth:sanctum');

    Route::get('server', [\App\Http\Controllers\Admin\ServerController::class, 'index'])->name('server.index');
    Route::get('server/create', [\App\Http\Controllers\Admin\ServerController::class, 'create'])->name('server.create');
    Route::get('server/create-bungee', [\App\Http\Controllers\Admin\ServerController::class, 'createBungee'])->name('server.create-bungee');
    Route::post('server/prefetch', [\App\Http\Controllers\Admin\ServerController::class, 'prefetch'])->name('server.prefetch');
    Route::post('server/force-scan', [\App\Http\Controllers\Admin\ServerController::class, 'postForceScan'])->name('server.force-scan');
    Route::post('server', [\App\Http\Controllers\Admin\ServerController::class, 'store'])->name('server.store');
    Route::post('server-bungee', [\App\Http\Controllers\Admin\ServerController::class, 'storeBungee'])->name('server-bungee.store');
    Route::get('server/{server}', [\App\Http\Controllers\Admin\ServerController::class, 'show'])->name('server.show');
    Route::get('server/{server}/edit', [\App\Http\Controllers\Admin\ServerController::class, 'edit'])->name('server.edit')->middleware('password.confirm');
    Route::put('server/{server}', [\App\Http\Controllers\Admin\ServerController::class, 'update'])->name('server.update');
    Route::put('server/{server}/bungee', [\App\Http\Controllers\Admin\ServerController::class, 'updateBungee'])->name('server.update.bungee');
    Route::delete('server/{server}', [\App\Http\Controllers\Admin\ServerController::class, 'destroy'])->name('server.delete')->middleware('password.confirm');
    Route::post('server/{server}/send-command', [\App\Http\Controllers\Admin\ServerController::class, 'postSendCommandToServer'])->name('server.command');
    Route::get('server/{server}/performance', [\App\Http\Controllers\Admin\ServerController::class, 'showPerformanceMonitor'])->name('server.show.perfmon');
    Route::get('server/{server}/insights', [\App\Http\Controllers\Admin\ServerController::class, 'showInsights'])->name('server.show.insights');
    Route::get('server/{server}/stats', [\App\Http\Controllers\Admin\ServerController::class, 'showStatistics'])->name('server.show.stats');

    Route::get('rank', [\App\Http\Controllers\Admin\RankController::class, 'index'])->name('rank.index');
    Route::get('rank/create', [\App\Http\Controllers\Admin\RankController::class, 'create'])->name('rank.create');
    Route::post('rank', [\App\Http\Controllers\Admin\RankController::class, 'store'])->name('rank.store');
    Route::post('rank/reset', [\App\Http\Controllers\Admin\RankController::class, 'resetRanks'])->name('rank.reset');
    Route::get('rank/{rank}', [\App\Http\Controllers\Admin\RankController::class, 'show'])->name('rank.show');
    Route::get('rank/{rank}/edit', [\App\Http\Controllers\Admin\RankController::class, 'edit'])->name('rank.edit');
    Route::put('rank/{rank}', [\App\Http\Controllers\Admin\RankController::class, 'update'])->name('rank.update');
    Route::delete('rank/{rank}', [\App\Http\Controllers\Admin\RankController::class, 'destroy'])->name('rank.delete');

    Route::get('news', [\App\Http\Controllers\Admin\NewsController::class, 'index'])->name('news.index');
    Route::get('news/create', [\App\Http\Controllers\Admin\NewsController::class, 'create'])->name('news.create');
    Route::post('news', [\App\Http\Controllers\Admin\NewsController::class, 'store'])->name('news.store');
    Route::get('news/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'show'])->name('news.show');
    Route::get('news/{news}/edit', [\App\Http\Controllers\Admin\NewsController::class, 'edit'])->name('news.edit');
    Route::put('news/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'update'])->name('news.update');
    Route::delete('news/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'destroy'])->name('news.delete');

    Route::get('role', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('role.index');
    Route::get('role/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('role.create');
    Route::post('role', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('role.store');
    Route::get('role/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('role.edit');
    Route::put('role/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('role.update');
    Route::delete('role/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('role.delete');

    Route::get('setting/general', [\App\Http\Controllers\Admin\Settings\GeneralSettingController::class, 'show'])->name('setting.general.show');
    Route::post('setting/general', [\App\Http\Controllers\Admin\Settings\GeneralSettingController::class, 'update'])->name('setting.general.update');
    Route::get('setting/plugin', [\App\Http\Controllers\Admin\Settings\PluginSettingController::class, 'show'])->name('setting.plugin.show');
    Route::post('setting/plugin', [\App\Http\Controllers\Admin\Settings\PluginSettingController::class, 'update'])->name('setting.plugin.update');
    Route::post('setting/plugin/keygen', [\App\Http\Controllers\Admin\Settings\PluginSettingController::class, 'regeneratePluginApiKeys'])->name('setting.plugin.keygen');
    Route::get('setting/theme', [\App\Http\Controllers\Admin\Settings\ThemeSettingController::class, 'show'])->name('setting.theme.show');
    Route::post('setting/theme', [\App\Http\Controllers\Admin\Settings\ThemeSettingController::class, 'update'])->name('setting.theme.update');
    Route::get('setting/player', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'show'])->name('setting.player.show');
    Route::post('setting/player', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'update'])->name('setting.player.update');
    Route::post('setting/player/validate-rating-expression', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'validateRatingExpression'])->name('setting.player.validate-rating-expression');
    Route::post('setting/player/validate-score-expression', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'validateScoreExpression'])->name('setting.player.validate-score-expression');

    Route::get('poll', [\App\Http\Controllers\Admin\PollController::class, 'index'])->name('poll.index');
    Route::get('poll/create', [\App\Http\Controllers\Admin\PollController::class, 'create'])->name('poll.create');
    Route::post('poll', [\App\Http\Controllers\Admin\PollController::class, 'store'])->name('poll.store');
    Route::delete('poll/{poll}', [\App\Http\Controllers\Admin\PollController::class, 'destroy'])->name('poll.delete');
    Route::put('poll/{poll}/lock', [\App\Http\Controllers\Admin\PollController::class, 'lock'])->name('poll.lock');
    Route::put('poll/{poll}/unlock', [\App\Http\Controllers\Admin\PollController::class, 'unlock'])->name('poll.unlock');

    Route::get('custom-page', [\App\Http\Controllers\Admin\CustomPageController::class, 'index'])->name('custom-page.index');
    Route::get('custom-page/create', [\App\Http\Controllers\Admin\CustomPageController::class, 'create'])->name('custom-page.create');
    Route::post('custom-page', [\App\Http\Controllers\Admin\CustomPageController::class, 'store'])->name('custom-page.store');
    Route::get('custom-page/{customPage}/edit', [\App\Http\Controllers\Admin\CustomPageController::class, 'edit'])->name('custom-page.edit');
    Route::put('custom-page/{customPage}', [\App\Http\Controllers\Admin\CustomPageController::class, 'update'])->name('custom-page.update');
    Route::delete('custom-page/{customPage}', [\App\Http\Controllers\Admin\CustomPageController::class, 'destroy'])->name('custom-page.delete');

    Route::get('session', [\App\Http\Controllers\Admin\SessionController::class, 'index'])->name('session.index');

    Route::get('badge', [\App\Http\Controllers\Admin\BadgeController::class, 'index'])->name('badge.index');
    Route::get('badge/create', [\App\Http\Controllers\Admin\BadgeController::class, 'create'])->name('badge.create');
    Route::post('badge', [\App\Http\Controllers\Admin\BadgeController::class, 'store'])->name('badge.store');
    Route::get('badge/{badge}/edit', [\App\Http\Controllers\Admin\BadgeController::class, 'edit'])->name('badge.edit');
    Route::put('badge/{badge}', [\App\Http\Controllers\Admin\BadgeController::class, 'update'])->name('badge.update');
    Route::delete('badge/{badge}', [\App\Http\Controllers\Admin\BadgeController::class, 'destroy'])->name('badge.delete');
});
