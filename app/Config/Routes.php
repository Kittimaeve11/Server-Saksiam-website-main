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

    // <---Banner --->
    $routes->get('bannerhomeapi', 'Admin\\ControlleBanner::bannerData');
    $routes->get('bannerapi/(:num)', 'Admin\\ControlleBanner::bannerDataID/$1');

    // <---Loan --->
    $routes->get('listloanapi', 'Admin\\ControlleLoan::listloanData');
    $routes->get('listloanformapi', 'Admin\\ControlleLoan::listloanFormData');
    $routes->get('listloanappapi', 'Admin\\ControlleLoan::listloanappData');
    $routes->get('loanapi/(:segment)', 'Admin\\ControlleLoan::loandetailData/$1');
    //appllication
    $routes->post('applicationapi', 'Admin\\ControllerApplication::createdataapplicationsAPI');
    //contactcmc
    $routes->post('applicationcmcapi', 'Admin\\Controlleinquiry::createdatacmcAPI');
    // <---Branch --->
    $routes->get('branchapi', 'Admin\\ControlleBranchlocations::showBranchlistdata');
    $routes->get('branchdataapi', 'Admin\\ControlleBranchlocations::showBranchWebsite');
        // <---History --->
    $routes->get('historyapi', 'Admin\\ControlleHistrory::index');
    // <---Contact --->
    $routes->get('contactapi', 'Admin\\ControlleContact::index');
      // <---Topic --->
    $routes->get('topicapi', 'Admin\\ControllerTopic::listgetTopiceData');
  // <---position --->
      $routes->get('positionapi', 'Admin\\ControllePosition::positionData');
    // <---Log --->
    $routes->post('logapi', 'Admin\\Controllerlog::uplogdataWeb');


    // <--- FAQ PAGE --->
    $routes->get('faqtypeapi', 'Admin\\ControllerFaqType::showFaqTypedatalist');
    $routes->get('faqapi', 'Admin\\ControllerFQA::showFqadatalist');


    // <--- EDITORIAL / NEWS ACTIVITY --->
    $routes->get('editorialtypeapi', 'Admin\\ControllerEditorialType::showEditorialTypedatalist');
    $routes->get('editoriaapi', 'Admin\\ControllerEditoria::showEditoriadatalist');
    $routes->get('editoriaapi/(:segment)', 'Admin\\ControllerEditoria::showEditoria/$1');
    $routes->get('editoriaapimain', 'Admin\\ControllerEditoria::showPinnedLatestEditoria');


    // <--- GALLERY PAGE --->
    $routes->get('galleryapi', 'Admin\\ControllerGallery::showGallerylist');
    $routes->get('galleryapi/(:num)', 'Admin\\ControllerGallery::showGallery/$1');


    // <--- POLICY PAGE --->
    $routes->get('policyapi', 'Admin\\ControllerPolicy::showPolicydatalist');
    $routes->get('policyapi/(:segment)', 'Admin\\ControllerPolicy::showPolicy/$1');


    // <--- DIRECTORS / TEAMS PAGE --->
    $routes->get('directorsapi', 'Admin\\ControllerDirectors::showDirectorsdatalist');


    // <--- MISSION PAGE --->
    $routes->get('missionapi', 'Admin\\ControllerMission::showMissionWebsite');


    // <--- REVIEW / VEDIO PAGE --->
    $routes->get('Reviewapi', 'Admin\\ControllerVedio::reviewWebsiteData');
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

// <--- type loan --->
    $routes->get('listtypeLoanAPI', 'Admin\\ControllerDocument::ShowDoslist');
    $routes->get('showtypeLoanAPI', 'Admin\\ControllerDocument::showDosData');
    $routes->post('createtypeLoanAPI', 'Admin\\ControllerDocument::createdataDoseAPI');
    $routes->get('showtypeLoanIDAPI/(:num)', 'Admin\\ControllerDocument::showDosDataID/$1');
    $routes->put('approvedtypeLoanAPI/(:num)', 'Admin\\ControllerDocument::approvedDosetStatusAPI/$1');
    $routes->post('updatetypeLoanAPI/(:num)', 'Admin\\ControllerDocument::updateDoseData/$1');
    $routes->delete('deletetypeLoanAPI/(:num)', 'Admin\\ControllerDocument::deleteDose/$1');
     $routes->get('conuttypeLoan', 'Admin\\ControllerDocument::countActiveStatusDose');
   // -------------------------------------------------------------------
// <--- loan --->
    $routes->get('showLoanPI', 'Admin\\ControlleLoan::showLoanData');
    $routes->post('createLoanAPI', 'Admin\\ControlleLoan::createdataLoanAPI');
    $routes->get('showLoanIDAPI/(:num)', 'Admin\\ControlleLoan::showLoanID/$1');
    $routes->post('updateLoanAPI/(:num)', 'Admin\\ControlleLoan::updateLoanData/$1');
    $routes->put('approvedLoanAPI/(:num)', 'Admin\\ControlleLoan::approvedloamData/$1');
    $routes->get('showLageMoveLoanAPI', 'Admin\\ControlleLoan::getLoanDataMove');
    $routes->get('showGraphLoanAPI', 'Admin\\ControlleLoan::showloangraphApi');
    $routes->put('updateLageMoveLoanAPI', 'Admin\\ControlleLoan::updateLoanMove');
    $routes->get('conutLoan', 'Admin\\ControlleLoan::countActiveStatusLoan');
   // -------------------------------------------------------------------
// <--- Branch --->
    $routes->get('showBranchAPI', 'Admin\\ControlleBranchlocations::showBranchData');
    $routes->get('areaapi', 'Admin\\ControlleBranchlocations::showBranchlistdataapi');
    $routes->post('createBranchAPI', 'Admin\\ControlleBranchlocations::upBranchDataAPI');
    $routes->post('branch/check-duplicate', 'Admin\ControlleBranchlocations::checkDuplicate');
    $routes->post('branch/check-Editduplicate', 'Admin\ControlleBranchlocations::checkEditDuplicate');
    $routes->post('branch/import', 'Admin\ControlleBranchlocations::importCSV');
    $routes->put('updateBranchAPI/(:num)', 'Admin\ControlleBranchlocations::updateBranchDetail/$1');
    $routes->get('showBranchAPI/(:num)', 'Admin\ControlleBranchlocations::showBranchDataID/$1');
    $routes->put('branch/update-csv', 'Admin\ControlleBranchlocations::updateCSV');
   // -------------------------------------------------------------------


 // <--- contact --->
// $routes->post('/api/createContactAPI', 'Admin\\ControllerContact::createdataContactAPI');
    $routes->get('showContactIDAPI', 'Admin\\ControlleContact::index');
    $routes->post('updateContactIDAPI', 'Admin\\ControlleContact::update');
    // -------------------------------------------------------------------

     // <---position --->
    $routes->get('showPositionlistAPI', 'Admin\\ControllePosition::positionsData');
    $routes->get('showPositionAPI', 'Admin\\ControllePosition::showPosition');
    $routes->post('createPositionAPI', 'Admin\\ControllePosition::createdataPositonAPI');
    $routes->get('showPositionIDAPI/(:num)', 'Admin\\ControllePosition::showPositionID/$1');
    $routes->put('updatePositionAPI/(:num)', 'Admin\\ControllePosition::updatePositionDetail/$1');
    $routes->get('showLageMovePositionAPI', 'Admin\\ControllePosition::getPositionataMove');
    $routes->put('updateLageMovePositionAPI', 'Admin\\ControllePosition::updatePositiondataMove');

    // <--- Log --->
    $routes->post('log', 'Admin\\Controllerlog::uplogdata');//*
    $routes->get('showlogAPI', 'Admin\\Controllerlog::showLog');

    // <--- FAQ TYPE --->
    $routes->get('showFaqTypelistAPI', 'Admin\\ControllerFaqType::showFaqTypedatalist');
    $routes->get('showFaqTypeAPI', 'Admin\\ControllerFaqType::showFaqTypeData');
    $routes->post('createFaqTypeAPI', 'Admin\\ControllerFaqType::uploadFaqTypeAPI');
    $routes->get('showFaqTypeIDAPI/(:num)', 'Admin\\ControllerFaqType::showFaqType/$1');
    $routes->put('updateFaqTypeAPI/(:num)', 'Admin\\ControllerFaqType::updateFaqTypeDetail/$1');
    $routes->post('updateFaqTypeMoveAPI', 'Admin\\ControllerFaqType::updateFaqTypeMove');
    $routes->delete('deleteFaqTypeAPI/(:num)', 'Admin\\ControllerFaqType::deleteFaqTypeData/$1');

    // -------------------------------------------------------------------

    // <--- FAQ --->
    $routes->get('showFqalistAPI', 'Admin\\ControllerFQA::showFqadatalist');
    $routes->get('showFaqQuestionlistAPI', 'Admin\\ControllerFQA::showFqadatalist');
    $routes->get('showFqaAPI', 'Admin\\ControllerFQA::showFqaData');
    $routes->get('showFaqQuestionAPI', 'Admin\\ControllerFQA::showFqaData');
    $routes->post('createFqaAPI', 'Admin\\ControllerFQA::uploadFqaAPI');
    $routes->post('createFaqQuestionAPI', 'Admin\\ControllerFQA::uploadFqaAPI');
    $routes->get('showFqaIDAPI/(:num)', 'Admin\\ControllerFQA::showFqa/$1');
    $routes->get('showFaqQuestionIDAPI/(:num)', 'Admin\\ControllerFQA::showFqa/$1');
    $routes->put('updateFqaAPI/(:num)', 'Admin\\ControllerFQA::updateFqaDetail/$1');
    $routes->put('updateFaqQuestionAPI/(:num)', 'Admin\\ControllerFQA::updateFqaDetail/$1');
    $routes->delete('deleteFqaAPI/(:num)', 'Admin\\ControllerFQA::deleteFqaData/$1');
    $routes->delete('deleteFaqQuestionAPI/(:num)', 'Admin\\ControllerFQA::deleteFqaData/$1');
    $routes->get('showLageMoveFqaAPI', 'Admin\\ControllerFQA::getFqaDataMove');
    $routes->get('showLageMoveFaqQuestionAPI', 'Admin\\ControllerFQA::getFqaDataMove');
    $routes->put('updateLageMoveFqaAPI', 'Admin\\ControllerFQA::updateFqaMove');
    $routes->put('updateLageMoveFaqQuestionAPI', 'Admin\\ControllerFQA::updateFqaMove');

    // -------------------------------------------------------------------

    // <--- EDITORIAL TYPE / ARTICLE TYPE --->
    $routes->get('showEditorialTypelistAPI', 'Admin\\ControllerEditorialType::showEditorialTypedatalist');
    $routes->get('showTypeEditoriallistAPI', 'Admin\\ControllerEditorialType::showEditorialTypedatalist');
    $routes->get('showEditorialTypeAPI', 'Admin\\ControllerEditorialType::showEditorialTypeData');
    $routes->get('showTypeEditorialAPI', 'Admin\\ControllerEditorialType::showEditorialTypeData');
    $routes->post('createEditorialTypeAPI', 'Admin\\ControllerEditorialType::uploadEditorialTypeAPI');
    $routes->post('createTypeEditorialAPI', 'Admin\\ControllerEditorialType::uploadEditorialTypeAPI');
    $routes->get('showEditorialTypeIDAPI/(:num)', 'Admin\\ControllerEditorialType::showEditorialType/$1');
    $routes->get('showTypeEditorialIDAPI/(:num)', 'Admin\\ControllerEditorialType::showEditorialType/$1');
    $routes->put('updateEditorialTypeAPI/(:num)', 'Admin\\ControllerEditorialType::updateEditorialTypeDetail/$1');
    $routes->put('updateTypeEditorialAPI/(:num)', 'Admin\\ControllerEditorialType::updateEditorialTypeDetail/$1');
    $routes->delete('deleteEditorialTypeAPI/(:num)', 'Admin\\ControllerEditorialType::deleteEditorialTypeData/$1');
    $routes->delete('deleteTypeEditorialAPI/(:num)', 'Admin\\ControllerEditorialType::deleteEditorialTypeData/$1');
    $routes->put('updateEditorialTypeMoveAPI', 'Admin\\ControllerEditorialType::updateEditorialTypeMove');
    $routes->put('updateTypeEditorialMoveAPI', 'Admin\\ControllerEditorialType::updateEditorialTypeMove');

    // -------------------------------------------------------------------

    // <--- EDITORIA / ARTICLE --->
    $routes->get('showEditoriallistAPI', 'Admin\\ControllerEditoria::showEditoriadatalist');
    $routes->get('showEditorialAPI', 'Admin\\ControllerEditoria::showEditoriaData');
    $routes->post('createEditorialAPI', 'Admin\\ControllerEditoria::uploadEditoriaAPI');
    $routes->get('showEditorialIDAPI/(:num)', 'Admin\\ControllerEditoria::showEditoria/$1');
    $routes->post('updateEditorialAPI/(:num)', 'Admin\\ControllerEditoria::updateEditoriaDetail/$1');
    $routes->put('updateEditorialAPI/(:num)', 'Admin\\ControllerEditoria::updateEditoriaDetail/$1');
    $routes->delete('deleteEditorialAPI/(:num)', 'Admin\\ControllerEditoria::deleteEditoriaData/$1');
    $routes->get('showEditorialArticlelistAPI', 'Admin\\ControllerEditoria::showEditoriadatalist');
    $routes->get('showEditorialArticleAPI', 'Admin\\ControllerEditoria::showEditoriaData');
    $routes->post('createEditorialArticleAPI', 'Admin\\ControllerEditoria::uploadEditoriaAPI');
    $routes->get('showEditorialArticleIDAPI/(:num)', 'Admin\\ControllerEditoria::showEditoria/$1');
    $routes->post('updateEditorialArticleAPI/(:num)', 'Admin\\ControllerEditoria::updateEditoriaDetail/$1');
    $routes->put('updateEditorialArticleAPI/(:num)', 'Admin\\ControllerEditoria::updateEditoriaDetail/$1');
    $routes->delete('deleteEditorialArticleAPI/(:num)', 'Admin\\ControllerEditoria::deleteEditoriaData/$1');

    // -------------------------------------------------------------------

    // <--- GALLERY --->
    $routes->get('showGallerylistAPI', 'Admin\\ControllerGallery::showGallerylist');
    $routes->get('showGalleryAPI', 'Admin\\ControllerGallery::showGalleryData');
    $routes->post('createGalleryAPI', 'Admin\\ControllerGallery::uploadGalleryAPI');
    $routes->get('showGalleryIDAPI/(:num)', 'Admin\\ControllerGallery::showGallery/$1');
    $routes->post('updateGalleryAPI/(:num)', 'Admin\\ControllerGallery::updateGalleryAPI/$1');
    $routes->put('updateGalleryAPI/(:num)', 'Admin\\ControllerGallery::updateGalleryAPI/$1');
    $routes->delete('deleteGalleryAPI/(:num)', 'Admin\\ControllerGallery::deleteGalleryData/$1');

    // -------------------------------------------------------------------

    // <--- POLICY --->
    $routes->get('showPolicylistAPI', 'Admin\\ControllerPolicy::showPolicydatalist');
    $routes->get('showPolicyAPI', 'Admin\\ControllerPolicy::showPolicyData');
    $routes->post('createPolicyAPI', 'Admin\\ControllerPolicy::uploadPolicyAPI');
    $routes->get('showPolicyIDAPI/(:num)', 'Admin\\ControllerPolicy::showPolicy/$1');
    $routes->post('updatePolicyAPI/(:num)', 'Admin\\ControllerPolicy::updatePolicyDetail/$1');
    $routes->put('updatePolicyAPI/(:num)', 'Admin\\ControllerPolicy::updatePolicyDetail/$1');
    $routes->delete('deletePolicyAPI/(:num)', 'Admin\\ControllerPolicy::deletePolicyData/$1');
    $routes->put('updatePolicyMoveAPI', 'Admin\\ControllerPolicy::updatePolicyMove');

    // -------------------------------------------------------------------

    // <--- DIRECTORS / COMMITTEE --->
    $routes->get('showDirectorslistAPI', 'Admin\\ControllerDirectors::showDirectorsdatalist');
    $routes->get('showDirectorsAPI', 'Admin\\ControllerDirectors::showDirectorsData');
    $routes->post('createDirectorsAPI', 'Admin\\ControllerDirectors::uploadDirectorsAPI');
    $routes->get('showDirectorsIDAPI/(:num)', 'Admin\\ControllerDirectors::showDirectors/$1');
    $routes->post('updateDirectorsAPI/(:num)', 'Admin\\ControllerDirectors::updateDirectorsDetail/$1');
    $routes->put('updateDirectorsAPI/(:num)', 'Admin\\ControllerDirectors::updateDirectorsDetail/$1');
    $routes->delete('deleteDirectorsAPI/(:num)', 'Admin\\ControllerDirectors::deleteDirectorsData/$1');
    $routes->put('updateDirectorsMoveAPI', 'Admin\\ControllerDirectors::updateDirectorsMove');
    $routes->get('showLageMoveDirectorsAPI', 'Admin\\ControllerDirectors::getDirectorsDataMove');
    $routes->put('updateLageMoveDirectorsAPI', 'Admin\\ControllerDirectors::updateDirectorsMove');
    $routes->get('showCommitteelistAPI', 'Admin\\ControllerDirectors::showDirectorsdatalist');
    $routes->get('showCommitteeAPI', 'Admin\\ControllerDirectors::showDirectorsData');
    $routes->post('createCommitteeAPI', 'Admin\\ControllerDirectors::uploadDirectorsAPI');
    $routes->get('showCommitteeIDAPI/(:num)', 'Admin\\ControllerDirectors::showDirectors/$1');
    $routes->post('updateCommitteeAPI/(:num)', 'Admin\\ControllerDirectors::updateDirectorsDetail/$1');
    $routes->put('updateCommitteeAPI/(:num)', 'Admin\\ControllerDirectors::updateDirectorsDetail/$1');
    $routes->delete('deleteCommitteeAPI/(:num)', 'Admin\\ControllerDirectors::deleteDirectorsData/$1');
    $routes->put('updateCommitteeMoveAPI', 'Admin\\ControllerDirectors::updateDirectorsMove');
    $routes->get('showLageMoveCommitteeAPI', 'Admin\\ControllerDirectors::getDirectorsDataMove');
    $routes->put('updateLageMoveCommitteeAPI', 'Admin\\ControllerDirectors::updateDirectorsMove');
    $routes->get('showcompanydirectorAPI', 'Admin\\ControllerDirectors::showDirectorsData');
    $routes->post('uploadTeamsAPI', 'Admin\\ControllerDirectors::uploadDirectorsAPI');
    $routes->get('showcompanydirectorIDAPI/(:num)', 'Admin\\ControllerDirectors::showDirectors/$1');
    $routes->post('updatecompanydirectorIDAPI/(:num)', 'Admin\\ControllerDirectors::updateDirectorsDetail/$1');
    $routes->delete('deletecompanydirectorIDAPI/(:num)', 'Admin\\ControllerDirectors::deleteDirectorsData/$1');
    $routes->get('showLageMovecompanydirectorAPI', 'Admin\\ControllerDirectors::getDirectorsDataMove');
    $routes->put('updateLageMoveTeamsAPI', 'Admin\\ControllerDirectors::updateDirectorsMove');

    // -------------------------------------------------------------------

    // <--- MISSION / MISSTION --->
    $routes->get('showmisstionAPI', 'Admin\\ControllerMission::showMissionData');
    $routes->post('createmisstionAPI', 'Admin\\ControllerMission::upMissionDataAPI');
    $routes->get('showmisstionIDAPI/(:num)', 'Admin\\ControllerMission::showMisstionDataID/$1');
    $routes->post('updatemisstionIDAPI/(:num)', 'Admin\\ControllerMission::updateMisstion/$1');
    $routes->delete('deletemisstionAPI/(:num)', 'Admin\\ControllerMission::deleteMisstionData/$1');
    $routes->get('showMissionAPI', 'Admin\\ControllerMission::showMissionData');
    $routes->post('createMissionAPI', 'Admin\\ControllerMission::upMissionDataAPI');
    $routes->get('showMissionIDAPI/(:num)', 'Admin\\ControllerMission::showMisstionDataID/$1');
    $routes->post('updateMissionIDAPI/(:num)', 'Admin\\ControllerMission::updateMisstion/$1');
    $routes->delete('deleteMissionAPI/(:num)', 'Admin\\ControllerMission::deleteMisstionData/$1');

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

    // <--- Vedio / Review --->
    $routes->get('showreviewAPI', 'Admin\\ControllerVedio::shoeReviewData');
    $routes->post('createreviewAPI', 'Admin\\ControllerVedio::uploadReviewAPI');
    $routes->get('showreviewIDAPI/(:num)', 'Admin\\ControllerVedio::showReview/$1');
    $routes->put('updatereviewIDAPI/(:num)', 'Admin\\ControllerVedio::updateReviewDetail/$1');
    $routes->post('updatereviewIDAPI/(:num)', 'Admin\\ControllerVedio::updateReviewDetail/$1');
    $routes->put('approvedreviewAPI/(:num)', 'Admin\\ControllerVedio::ApprovedReviewStatusAPI/$1');
    $routes->post('approvedreviewAPI/(:num)', 'Admin\\ControllerVedio::ApprovedReviewStatusAPI/$1');
    $routes->delete('deletedreviewAPI/(:num)', 'Admin\\ControllerVedio::deleteReview/$1');
    $routes->get('showGraphreviewAPI', 'Admin\\ControllerVedio::showVediographApi');
    $routes->get('coutReview', 'Admin\\ControllerVedio::countActiveStatusReview');
    $routes->get('showVedioAPI', 'Admin\\ControllerVedio::shoeReviewData');
    $routes->post('createVedioAPI', 'Admin\\ControllerVedio::uploadReviewAPI');
    $routes->get('showVedioIDAPI/(:num)', 'Admin\\ControllerVedio::showReview/$1');
    $routes->put('updateVedioAPI/(:num)', 'Admin\\ControllerVedio::updateReviewDetail/$1');
    $routes->post('updateVedioAPI/(:num)', 'Admin\\ControllerVedio::updateReviewDetail/$1');
    $routes->delete('deleteVedioAPI/(:num)', 'Admin\\ControllerVedio::deleteReview/$1');
});

// <--- Mode --->
$routes->group('/api/website', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('theme-mode', 'Website::getThemeMode');
    $routes->post('theme-mode', 'Website::setThemeMode');

    // <--- Log --->
    $routes->post('log', 'Admin\\Controllerlog::uplogdata'); //*
    $routes->get('showlogAPI', 'Admin\\Controllerlog::showLog');
});
