<html>
<head><title>OCWDB-class-check.php</title></head>
<body>

<?php

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once('config.php');
require_once('library.php');
require_once('lib/ocw_init.php') ;
require_once('lib/class/OCWDB.class.php');

$ocwdb = new OCWDB;

// $course_name = $ocwdb -> getCourseName($course_id='41', $lang='ja');
// echo "<hr> course_name: ".$course_name."<br>" ;

// $PageNameArray = $ocwdb -> getPageNameArray($page_id='1492'); 
// echo "<hr> PageNameArray: ".$PageNameArray."<br>" ;  
// print_r($PageNameArray);

// $PageName = $ocwdb -> getPageName($page_id='1492', $glue=' > ') ; 
// echo "<hr> PageName: ".$PageName."<br>" ;  
// print_r($PageName);

// $PageFilename = $ocwdb -> getPageFilename($page_id='1492') ; 
// echo "<hr> PageFilename: ".$PageFilename."<br>" ;  
// print_r($PageFilename);

// $DepartmentName = $ocwdb -> getDepartmentName($department_id='16', $lang='ja') ; 
// echo "<hr> DepartmentName: ".$DepartmentName."<br>" ;  
// print_r($DepartmentName);

// $DepartmentTopicPath = $ocwdb -> getDepartmentTopicPath($department_id='16', $lang='ja') ; 
// echo "<hr> DepartmentTopicPath: ".$DepartmentTopicPath."<br>" ;  
// print_r($DepartmentTopicPath);

// $DivisionName = $ocwdb -> getDivisionName($course_id='41', $lang='ja') ; 
// echo "<hr> DivisionName: ".$DivisionName."<br>" ;  
// print_r($DivisionName);

// $StatusName = $ocwdb -> getStatusName($status_code='01') ; 
// echo "<hr> StatusName: ".$StatusName."<br>" ;  
// print_r($StatusName);

// $TermName = $ocwdb -> getTermName($term_code='4', $lang = 'ja')  ; 
// echo "<hr> TermName: ".$TermName."<br>" ;  
// print_r($TermName);
   

// $YearAndTerm = $ocwdb -> getYearAndTerm($course_id='41', $lang='ja');;
// echo "<hr> YearAndTerm: ".$YearAndTerm."<br>" ;  

// $TermCode = $ocwdb -> getTermCode($course_id='41');
// echo "<hr> TermCode: ".$TermCode."<br>" ;  

// $Contents = $ocwdb -> getContents($course_id='41', $page_type='51', $contents_type='1301') ;
// echo "<hr> Contents: ".$Contents."<br>" ;  

// $CourseOverview = $ocwdb -> getCourseOverview($course_id='41', $lang='ja') ;
// echo "<hr> CourseOverview: ".$CourseOverview."<br>" ;  

// $InstructorNameAndPosition = $ocwdb -> getInstructorNameAndPosition($instructor_id='41', $lang="ja");
// echo "<hr> InstructorNameAndPosition: ".$InstructorNameAndPosition."<br>" ;  
// print_r($InstructorNameAndPosition);

// $CourseInstructorInfo = $ocwdb -> getCourseInstructorInfo($course_id='41', $lang='ja') ;
// echo "<hr> CourseInstructorInfo: ".$CourseInstructorInfo."<br>" ;  
// print_r($CourseInstructorInfo);

// $CourseVsyllabus = $ocwdb -> getCourseVsyllabus($course_id='179', $lang='ja', $video_lang='ja'); 
// echo "<hr> CourseVsyllabus: ".$CourseVsyllabus."<br>" ;  
// print_r($CourseVsyllabus);

$MeetingTime = $ocwdb -> getMeetingTime($course_id='41', $lang='ja');
echo "<br> MeetingTime: ".$MeetingTime."<br>" ;  
print_r($MeetingTime);

// $TermName = $ocwdb -> getTermName($term_code='10', $lang = 'ja');
// echo "<br> TermName: ".$TermName."<br>" ;  
// print_r($TermName);

// $Archive = $ocwdb -> getArchive($course_id='74');
// echo "<br> Archive: ".$Archive."<br>" ;  
// print_r($Archive);

// $DepartmentList = $ocwdb -> getDepartmentList($lang='ja', $type=SCT_SHOW_OK, $child=true);
// echo "<hr> DepartmentList: <br><br>".$DepartmentList."<br>" ;  
// print_r($DepartmentList);

// $DepartmentAbbrList = $ocwdb -> getDepartmentAbbrList($lang='ja', $type=SCT_SHOW_OK, $child=true);
// echo "<hr> DepartmentAbbrList: <br><br>".$DepartmentAbbrList."<br>" ;  
// print_r($DepartmentAbbrList);

// $PageFilenameList = $ocwdb -> getPageFilenameList($course_id='41', $lang='ja')  ;
// echo "<hr> PageFilenameList: <br><br>".$PageFilenameList."<br>" ;  
// print_r($PageFilenameList);

// $AllCourseList = $ocwdb -> getAllCourseList($department_id=null, $lang='ja')  ;
// echo "<hr> AllCourseList: <br><br>".$AllCourseList."<br>" ;  
// print_r($AllCourseList);

// $IssuableCourseList = $ocwdb -> getIssuableCourseList($department_id=null, $lang='ja')  ;
// echo "<hr> IssuableCourseList: <br><br>".$IssuableCourseList."<br>" ;  
// print_r($IssuableCourseList);

// $NowShowingCourseList = $ocwdb -> getNowShowingCourseList($department_id=null, $lang='ja')  ;
// echo "<hr> NowShowingCourseList: <br><br>".$NowShowingCourseList."<br>" ;  
// print_r($NowShowingCourseList);

// $RelatedCourseList = $ocwdb -> getRelatedCourseList($department_id='9', $lang='ja', $course_status='')  ;
// echo "<hr> RelatedCourseList: <br><br>".$RelatedCourseList."<br>" ;  
// print_r($RelatedCourseList);

// $PageLang = $ocwdb -> getPageLang($page_id='1492')  ;
// echo "<hr> PageLang: <br><br>".$PageLang."<br>" ;  
// print_r($PageLang);

// $PageIdByPageType = $ocwdb -> getPageIdByPageType($course_id='41', $page_type_filename='index', $lang='ja') ;
// echo "<hr> PageIdByPageType: <br><br>".$PageIdByPageType."<br>" ;  
// print_r($PageIdByPageType);

// $CourseIdByPageId = $ocwdb -> getCourseIdByPageId($page_id='41') ;
// echo "<hr> CourseIdByPageId: <br><br>".$CourseIdByPageId."<br>" ;  
// print_r($CourseIdByPageId);

// $DepartmentIdByCourseId = $ocwdb -> getDepartmentIdByCourseId($course_id='41') ;
// echo "<hr> DepartmentIdByCourseId: <br><br>".$DepartmentIdByCourseId."<br>" ;  
// print_r($DepartmentIdByCourseId);

// $DepartmentAbbrByCourseID = $ocwdb -> getDepartmentAbbrByCourseID($course_id='41') ;
// echo "<hr> DepartmentAbbrByCourseID: <br><br>".$DepartmentAbbrByCourseID."<br>" ;  
// print_r($DepartmentAbbrByCourseID);

// $DepartmentIdByDepartmentAbbr = $ocwdb -> getDepartmentIdByDepartmentAbbr($department_abbr = 'soec') ;
// echo "<hr> DepartmentIdByDepartmentAbbr: <br><br>".$DepartmentIdByDepartmentAbbr."<br>" ;  
// print_r($DepartmentIdByDepartmentAbbr);

// $_SESSION['userid'] = 'yamazato';

// $PageStatus = $ocwdb -> setPageStatus($page_id='1492', $status_code='08', $flg=true) ;
// echo "<hr> PageStatus: <br><br>".var_export($PageStatus)."<br>" ;  
// print_r($PageStatus);

// 失敗
// $CourseIdByFileId = $ocwdb -> getCourseIdByFileId($file_id='402') ;
// echo "<hr> CourseIdByFileId: <br><br>".$CourseIdByFileId."<br>" ;  
// var_dump($CourseIdByFileId);

// $ExtensionByFileId = $ocwdb -> getExtensionByFileId($file_id='402') ;
// echo "<hr> ExtensionByFileId: <br><br>".$ExtensionByFileId."<br>" ;  
// var_dump($ExtensionByFileId);

// $FileName = $ocwdb -> getFileName($file_gid='402') ;
// echo "<hr> FileName : <br><br>".$FileName."<br>" ;  
// var_dump($FileName);

// $VsyllabusFileName = $ocwdb -> getVsyllabusFileName($vsyllabus_id='428') ;
// echo "<hr> VsyllabusFileName : <br><br>".$VsyllabusFileName."<br>" ;  
// var_dump($VsyllabusFileName);

// $CurrentFileId = $ocwdb -> getCurrentFileId($file_gid='402') ;
// echo "<hr> CurrentFileId : <br><br>".$CurrentFileId."<br>" ;  
// var_dump($CurrentFileId);

// $FileGidByFileId = $ocwdb -> getFileGidByFileId($file_id='402') ;
// echo "<hr> FileGidByFileId : <br><br>".$FileGidByFileId."<br>" ;  
// var_dump($FileGidByFileId);

// // 未対応 chflgFileGroup($file_gid, $flg)

// $MimeTypeId = $ocwdb -> getMimeTypeId($mime_type='text/plain', $extension=null) ;
// echo "<hr> MimeTypeId : <br><br>".$MimeTypeId."<br>" ;  
// var_dump($MimeTypeId);

// // 未対応　getBakName($filepath)

// $IndexImageGid = $ocwdb -> getIndexImageGid($course_id='41') ;
// echo "<hr> IndexImageGid : <br><br>".$IndexImageGid."<br>" ;  
// var_dump($IndexImageGid);

$EventListInCourse = $ocwdb -> getEventListInCourse($course_id='41', $start=null, $disp_num=null) ;
echo "<hr> EventListInCourse : ".$EventListInCourse."<br>" ;  
var_dump($EventListInCourse);


$NotSeenEventsNumber = $ocwdb -> getNotSeenEventsNumber($page_id='742', $user_id='yamazato') ;
echo "<hr> NotSeenEventsNumber : ".$NotSeenEventsNumber."<br>" ;  
var_dump($NotSeenEventsNumber);

$setEvent = $ocwdb -> setEvent($result, $type, $description) ;
echo "<hr> setEvent : <br><br>".$setEvent."<br>" ;  
var_dump($setEvent);


$OkEvent = $ocwdb -> setOkEvent($type, $description) ;
echo "<hr> OkEvent : <br><br>".$OkEvent."<br>" ;  
var_dump($OkEvent);

$ErrorEvent = $ocwdb -> setErrorEvent($type, $description) ;
echo "<hr> ErrorEvent : <br><br>".$ErrorEvent."<br>" ;  
var_dump($ErrorEvent);

$EventRelation = $ocwdb -> setEventRelation($event_id, $relation_type, $relation_id) ;
echo "<hr> EventRelation : <br><br>".$EventRelation."<br>" ;  
var_dump($EventRelation);


echo "<hr><br>";


?>
</body>
</html>