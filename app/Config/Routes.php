<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// <--- test --->
$routes->get('api/test', 'Home::gettest');

// <--- api key --->
// $routes->post('/api/create-key', 'Home::createApiKey');

// <-- api Web Sola  -->
// -------------------------------------------------------------------

$routes->group('api', function ($routes): void {
    $routes->get('bannerhomeapi', 'Admin\\ControlleBanner::bannerData');
    $routes->get('bannerapi/(:num)', 'Admin\\ControlleBanner::bannerDataID/$1');
    $routes->get('branchapi', 'Admin\\ControlleBranchlocations::showBranchlistdata');
    $routes->get('branchdataapi', 'Admin\\ControlleBranchlocations::showBranchWebsite');

    // <--- faqpage --->
    $routes->get('faqtypeapi', 'Admin\\ControllerFaqType::showFaqTypedatalist');
    // $routes->post('refreshTokenapi', 'ControllerAuth::refreshTokenProsonal');
});




// <-- api Manager solar -->
// -------------------------------------------------------------------
// <---Auth --->
$routes->group('api/auth', function ($routes): void {
    $routes->post('loginapi', 'ControllerAuth::loginProsonal');
    $routes->post('refreshTokenapi', 'ControllerAuth::refreshTokenProsonal');
});


$routes->group('api/auther', ['filter' => 'jwt_auth'], function ($routes) {
    // <--- Auth and User --->
    $routes->get('profile', 'ControllerAuth::prosonalProfile');
    $routes->get('showUserAPI', 'ControllerAuth::showUserDAta');
    $routes->get('showUserAPI/(:num)', 'ControllerAuth::showUserDAtaID/$1');
    $routes->post('updateUserAPI/(:num)', 'ControllerAuth::updateUserDAtaID/$1');
    $routes->post('updateUserProfile', 'ControllerAuth::updatePersonalProfile');
    $routes->post('registerapi', 'ControllerAuth::regiterProsonal');
    $routes->post('resetPasswordapi', 'ControllerAuth::resetPassword');
    $routes->post('changePasswordapi', 'ControllerAuth::changePassword');
    $routes->post('logoutapi', 'ControllerAuth::logoutProsonal');

    // <--- FAQ TYPE --->
    $routes->get('showFaqTypelistAPI', 'Admin\\ControllerFaqType::showFaqTypedatalist');
    $routes->get('showFaqTypeAPI', 'Admin\\ControllerFaqType::showFaqTypeData');
    $routes->post('createFaqTypeAPI', 'Admin\\ControllerFaqType::uploadFaqTypeAPI');
    $routes->get('showFaqTypeIDAPI/(:num)', 'Admin\\ControllerFaqType::showFaqType/$1');
    $routes->put('updateFaqTypeAPI/(:num)', 'Admin\\ControllerFaqType::updateFaqTypeDetail/$1');
    $routes->post('updateFaqTypeMoveAPI', 'Admin\\ControllerFaqType::updateFaqTypeMove');
    // -------------------------------------------------------------------

    // <--- RolePermission --->
    $routes->get('showRolelistAPI', 'Admin\\RoleController::showRoledatalist');
    $routes->get('showRolePermissionAPI', 'Admin\\RoleController::showRoleData');
    $routes->get('showPermissionAPI', 'Admin\\RoleController::showPermissionsAPI');
    $routes->post('createRolePermissionAPI', 'Admin\\RoleController::uploandRoleApi');
    $routes->get('showRolePermissionAPI/(:num)', 'Admin\\RoleController::showRoleID/$1');
    $routes->put('updateRolePermissionAPI/(:num)', 'Admin\\RoleController::updateRoleApi/$1');
    // -------------------------------------------------------------------

    // <--- Banner --->
    $routes->get('showbannerAPI', 'Admin\\ControlleBanner::showBannerData');
    $routes->post('createbannerAPI', 'Admin\\ControlleBanner::createdataBannerAPI');
    $routes->post('updatebannerAPI/(:num)', 'Admin\\ControlleBanner::updateBannerDAta/$1');
    $routes->get('showbannerIDAPI/(:num)', 'Admin\\ControlleBanner::showBannerDataID/$1');
    $routes->delete('deletebannerAPI/(:num)', 'Admin\\ControlleBanner::deleteBanner/$1');
    $routes->get('showLageMovebannerAPI', 'Admin\\ControlleBanner::getBannerDataMove');
    $routes->put('updateLageMovebannerAPI', 'Admin\\ControlleBanner::updateBannerMove');
    // -------------------------------------------------------------------  

    // <--- Topic --->
    $routes->get('showTopiclistAPI', 'Admin\\ControllerTopic::showTopicdetaillist');
    $routes->get('showTopicAPI', 'Admin\\ControllerTopic::showTopicData');
    $routes->post('createTopicAPI', 'Admin\\ControllerTopic::upTopicDataAPI');
    $routes->get('showTopicIDAPI/(:num)', 'Admin\\ControllerTopic::showTopicDataID/$1');
    $routes->put('updateTopicAPI/(:num)', 'Admin\\ControllerTopic::updateTopicDetail/$1');
    $routes->delete('deleteTopicAPI/(:num)', 'Admin\\ControllerTopic::deleteTopicData/$1');
    $routes->get('showLageMoveTopicAPI', 'Admin\\ControllerTopic::getTopicDataMove');
    $routes->put('updateLageMoveTopicAPI', 'Admin\\ControllerTopic::updateTopicdataMove');
    // -------------------------------------------------------------------
    // <--- Branch --->

    $routes->get('showBranchAPI', 'Admin\\ControlleBranchlocations::showBranchData');

    // -------------------------------------------------------------------


    // <--- contact --->
    // $routes->post('/api/createContactAPI', 'Admin\\ControllerContact::createdataContactAPI');
    $routes->get('showContactIDAPI/(:num)', 'Admin\\ControllerContact::showContactDaTaID/$1');
    $routes->post('updateContactIDAPI/(:num)', 'Admin\\ControllerContact::updateContactData/$1');
    // -------------------------------------------------------------------
    // <--- Log --->
    $routes->post('log', 'Admin\\Controllerlog::uplogdata'); //*
});
