<?php

require_once('OCWVAR.class.php');

// OCWDB クラス
//  - DB にかかわるクラス
//
// * 名称の取得
// PDO  - getCourseName($course_id, $lang)  
// PDO  - getPageNameArray($page_id)
// PDO  - getPageName($page_id, $glue)
// PDO  - getPageFilename($page_id)
// PDO  - getDepartmentName($department_id, $lang) : 所属グループ
// PDO  - getDepartmentTopicPath($department_id, $lang)
// PDO  - getDivisionName($course_id, $lang) : 開講部局
// PDO  - getInstructorNameAndPosition($instructor_id, $lang)
// PDO  - getStatusName($status_code)
// PDO  - getTermName($term_code, $lang)
//
// * コンテンツの取得
// PDO  - getYearAndTerm($course_id, $lang)
// PDO  - getTermCode($course_id)
// PDO  - getArchive($course_id)
// PDO失敗  - getMeetingTime($course_id, $lang)
// PDO  - getContents($course_id, $page_type, $contents_type)
// PDO  - getCourseOverview($course_id, $lang)
// PDO  - getCourseInstructorInfo($course_id, $lang)
// PDO  - getCourseVsyllabus($course_id, $video_lang, $lang)
//
// * リストの取得
// PDO  - getDepartmentList($lang, $type, $child)
// PDO  - getDepartmentAbbrList($lang, $type, $child)
// PDO  - getPageFilenameList($course_id, $lang)
// PDO  - getAllCourseList($department_id, $lang)
// PDO  - getIssuableCourseList($department_id, $lang)
// PDO  - getNowShowingCourseList($department_id, $lang)
// PDO  - getRelatedCourseList($department_id, $lang, $course_status)
//
// * 言語関連
// PDO  - getPageLang($page_id)
//
// * ID の取得
// PDO  - getPageIdByPageType($course_id, $page_type_filename, $lang)
// PDO  - getCourseIdByPageId($page_id)
// PDO  - getDepartmentIdByCourseId($course_id)
// PDO  - getDepartmentAbbrByCourseID($course_id)
// PDO  - getDepartmentIdByDepartmentAbbr($department_abbr)
//
// * ステータス関連
//   - setPageStatus($page_id, $status_code, $flg)
//   - isSetPageStatus($page_id, $status_code)
//   - existSetPageStatus($course_id, $status_code)
//   - setCourseStatus($course_id, $status_code, $flg, $lang)
//   - isSetCourseStatus($course_id, $status_code, $lang)
//   - backToChecking($course_id, $lang)
//   - isWorking($course_id, $lang)
//   - setDepartmentStatus($department_id, $lang, $status_code, $flg)
//   - existLectNotes($course_id,$lang)
//   - whenReleased($course_id, $lang)
//
// * ファイル関連
//   - getCourseIdByFileGid($file_gid)
//   - getCourseIdByFileId($file_id)
//   - getFileName($file_gid)
//   - getVsyllabusFileName($vsyllabus_id)
//   - getCurrentFileId($file_gid)
//   - getFileGidByFileId($file_id)
//   - getExtensionByFileId($file_id)
//   - chflgFileGroup($file_gid, $flg)
//   - getMimeTypeId($mime_type, $extension)
//   - getBakName($filepath)
//   - getIndexImageGid($course_id)
//
// * イベント関連
//   - getEventListInCourse($course_id)
//   - getNotSeenEventsNumber($page_id, $userid)
//   - setEvent($result, $type, $description)
//   - setOkEvent($type, $description)
//   - setErrorEvent($type, $description)
//   - setEventRelation($event_id, $relation_type, $relation_id)

class OCWDB
{
    //////////////////////////////
  //
  // * 名称の取得
  //
  //////////////////////////////
  public static function getCourseName($course_id, $lang='ja')
  {
      global $db;
    // コース ID が不正でないか.
    if (empty($course_id) || !ctype_digit("$course_id")) {
        return false;
    }
    
      if ($lang == 'ja') {
          $column = 'course_name';
      } else {
          $column = 'course_name_e';
      }
    
    try {
      // $db = new PDO(DSN);
      // SQLの準備
      $sql = $db->prepare("SELECT $column FROM course WHERE course_id = $course_id");
    //   print_r($sql);
      // SQL の発行
      $sql->execute();
    //   print_r($sql);
      $course_name = $sql->fetch(PDO::FETCH_BOTH);
    }catch(PDOException $e) {
        //エラー出力
        echo "データベースエラー（PDOエラー）";
        var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
          return false;
      }
    //   echo "<br>column : ".$column."<br>" ;
    //   print_r($course_name) ;
      return $course_name[$column];
  }
  
    public static function getPageNameArray($page_id)
    {
        global $db;
        $ocwdb = new OCWDB();
    
    // ページの言語を取得.
    switch ($lang = $ocwdb->getPageLang($page_id)) {
    case 'ja':
      $column = 'name';
      break;
      
    case 'en':
      $column = 'name_e';
      break;
      
    default:
      return false;
    }
    
    // page_type_master, page テーブルの順
    $sql=  "SELECT ptm.$column as ptm, p.$column as p
                FROM pages p, page_type_master ptm
                WHERE p.page_id = $page_id AND
                      p.page_type = ptm.page_type_code; ";
        // $res = $db->getRow($sql);
    
        // if (DB::isError($res)) {
        //     return false;
        // } else {
        //     return $res;
        // } // 配列で返す.
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $res = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            return $res;        
    }
  
  // ページ名をサブページの場合, glue でくっつけて返す.
  public static function getPageName($page_id, $glue=' > ')
  {
      $ocwdb = new OCWDB();
      $array = $ocwdb->getPageNameArray($page_id);
    //   print_r(!$array);
      if (!$array = $ocwdb->getPageNameArray($page_id)) {
          return false;
      } else {
          return (empty($array['p'])) ? $array['ptm'] : implode($glue, $array);
      }
  }
  
  // ページのファイルネーム（発行時のテンプレート名）を返す.
  public static function getPageFilename($page_id)
  {
      global $db;
    
      if (!OCWVAR::isId($page_id)) {
          return false;
      }
    
      $sql = "SELECT COALESCE(p.filename, ptm.filename)
               FROM pages p, page_type_master ptm
               WHERE ptm.page_type_code = p.page_type AND
                     p.page_id = $page_id;";
    //   $res = $db->getOne($sql);
    
    //   if (DB::isError($res)) {
    //       return false;
    //   } else {
    //       return $res;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $res = $sql_pdo->fetch(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        return $res[0];      
  }
  
    public static function getDepartmentName($department_id, $lang='ja')
    {
        global $db;
        $ocwvar = new OCWVAR();
    
        if (!$ocwvar->isId($department_id)) {
            return false;
        }
    
        if ($lang == 'ja') {
            $column = 'department_name';
        } else {
            $column = 'department_name_e';
        }
    
        $sql= "SELECT $column
             FROM department d
             WHERE d.department_id = $department_id;
         ";
        // $res =& $db->getOne($sql);
    
        // if (!DB::isError($res)) {
        //     return $res;
        // } else {
        //     return false;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $res = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            return $res[0];        
    }
  
    public static function getDepartmentTopicPath($department_id, $lang='ja')
    {
        global $db;
        $ocwvar = new OCWVAR();
    
        if (!$ocwvar->isId($department_id)) {
            return false;
        }
    
        if ($lang == 'ja') {
            $column = 'department_name';
        } else {
            $column = 'department_name_e';
        }
    
        $path = array();
        $count = MAX_TOPIC_PATH_LENGTH;
        while ($count-- > 0 && !empty($department_id)) {
            $sql= "SELECT $column as name, department_abbr as abbr, department_parent_id as parent
             FROM department d
             WHERE d.department_id = $department_id;";
            // $res =& $db->getRow($sql);
            // if (!DB::isError($res)) {
            //     $topic = array('text' => $res['name'], 'mode' => 'l', 'page_type' => $res['abbr']);
            //     $path[] = $topic;
            //     $department_id = $res['parent'];
            // } else {
            //     return false;
            // }
            try {
                $sql_pdo = $db->prepare($sql);
                $sql_pdo->execute();
                $res = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
              }catch(PDOException $e) {
                  echo "データベースエラー（PDOエラー）";
                  // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                    return false;
                }
                
                 $topic = array('text' => $res['name'], 'mode' => 'l', 'page_type' => $res['abbr']);
                 $path[] = $topic;
                 $department_id = $res['parent'];
        }
        return array_reverse($path);
    }
  
    public static function getDivisionName($course_id, $lang='ja')
    {
        global $db;
    // コース ID が不正でないか.
    if (empty($course_id) || !ctype_digit("$course_id")) {
        return false;
    }
    
        if ($lang == 'ja') {
            $column = 'division_name';
        } else {
            $column = 'division_name_e';
        }
    
        $sql = "SELECT dcm.$column FROM course c, division_code_master dcm
            WHERE c.course_id = $course_id AND c.division= dcm.division_code";
        // $name = $db->getOne($sql);
    
        // if (DB::isError($name)) {
        //     return false;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $name = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            
        return $name[0];
    }
  
    public static function getInstructorNameAndPosition($instructor_id, $lang="ja")
    {
        global $db;
    
        if (!OCWVAR::isId($instructor_id)) {
            return false;
        }
    
        if ($lang == "ja") {
            $q_name = "instructor_name";
            $q_position = "instructor_position";
        } else {
            $q_name = "instructor_name_e";
            $q_position = "instructor_position_e";
        }
    
        $sql=  "SELECT $q_name as name, $q_position as position
     FROM instructor i
           WHERE i.instructor_id = $instructor_id
    ";
        // $name_pos = $db->getRow($sql);
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $name_pos = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            return $name_pos;
    }
  
    public static function getStatusName($status_code)
    {
        global $db;
    
        if (!ctype_digit("$status_code")) {
            return false;
        }
    
        $sql = "SELECT status FROM status_code_master WHERE code='$status_code';";
        // $status_name = $db->getOne($sql);
    
        // if (DB::isError($status_name)) {
        //     return false;
        // } else {
        //     return $status_name;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $status_name = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            return $status_name[0];        
    }
  
    public static function getTermName($term_code, $lang = 'ja')
    {
        global $db;
    
        if (!ctype_digit("$term_code")) {
            return false;
        }
    
        if ($lang == 'ja') {
            $column = 'name';
        } else {
            $column = 'name_e';
        }
    
        $sql = "SELECT $column FROM term_code_master WHERE term_code='$term_code';";
        // $term_name = $db->getOne($sql);
    
        // if (DB::isError($term_name)) {
        //     return false;
        // } else {
        //     return $term_name;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $term_name = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            return $term_name[0];
            
    }
  
  //////////////////////////////
  //
  // * コンテンツの取得
  //
  //////////////////////////////

  // 2006年度前期 / 2006 Summer Term のような出力を得る.
  public static function getYearAndTerm($course_id, $lang='ja')
  {
      global $db;
    
      if (!OCWVAR::isId($course_id)) {
          return false;
      }
    
      if ($lang == 'ja') {
          $column = "c.year||'年度'||tcm.name";
      } else {
          $column = "c.year||' '||tcm.name_e";
      }
    
      $sql = "SELECT $column FROM course c, term_code_master tcm
             WHERE c.course_id = $course_id AND c.term = tcm.term_code;";
          
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $res = $sql_pdo->fetch(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          return false;
        }
        // print_r($res);
          return $res[0];
      
  }

  // course テーブルの archive を得る.
  public static function getArchive($course_id)
  {
      global $db;
    
      if (!OCWVAR::isId($course_id)) {
          return false;
      }
    
      $sql = "SELECT archive FROM course
             WHERE course_id = $course_id;";
    //   $res = $db->getOne($sql);
    
    //   if (DB::isError($res)) {
    //       return false;
    //   } else {
    //       return $res;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $res = $sql_pdo->fetch(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        return $res[0];
              
  }
  
  // course テーブルの term を得る.
  public static function getTermCode($course_id)
  {
      global $db;
    
      if (!OCWVAR::isId($course_id)) {
          return false;
      }
    
      $sql = "SELECT term FROM course
             WHERE course_id = $course_id;";
          
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $res = $sql_pdo->fetch(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          return false;
        }

        return $res[0];
  }
  
  // 火曜1, 2限 / 前期火曜1限\n後期水曜2限 のような出力を得る.
  public static function getMeetingTime($course_id, $lang='ja')
  {
      global $db;
    
      global $DAY_LIST;
      global $DAY_LIST_E;
      global $TIME_LIST;
    
      $mt = array();
    
    // 入力は適正か.
    if (!OCWVAR::isId($course_id)) {
        return false;
    }
    
    // 言語によって曜日名の表示を変える.
    if ($lang == 'ja') {
        $_day_list = $DAY_LIST;
    } else {
        $_day_list = $DAY_LIST_E;
    }
    
    // 指定されたコースで登録されている授業時間の開講学期の種類を問い合わせ.
    $sql = "SELECT term FROM meeting_time WHERE course_id = $course_id GROUP BY term;";
    // $sql = "SELECT term FROM meeting_time WHERE course_id = $course_id ;";
    //   $terms =& $db->getCol($sql);
    //   if (DB::isError($terms)) {
    //       return false;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $terms = $sql_pdo->fetchALL(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
        //   var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        // return $res;
        echo "<br> terms : ";
        print_r($terms[0]);
        
    // 登録なしのときは, 空文字列を返す.
    if (count($terms) == 0) {
        return '';
    }
    
    // 各学期について回す.
    foreach ($terms as $term_code) {
        // 該当学期の講義時間を曜日ごとにグループ化して取得.
      $sql = "SELECT mt.day, mt.time FROM meeting_time mt
               WHERE mt.course_id = $course_id AND mt.term = '$term_code';";
        // $res =& $db->getAssoc($sql, false, array(), DB_FETCHMODE_ASSOC, true);
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $res = $sql_pdo->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
            //   var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            echo "<br> getMeetingTime : ";
            print_r($res);
            // return $res;
      // 曜日について回す.
      $_mt_term = array();
        foreach ($_day_list as $day_code => $day_name) {
            // 該当曜日に登録があるとき.
        if (count($res[$day_code]) > 0) {
            $times_of_the_day = '';
          
          // 日本語のとき.
          if ($lang == 'ja') {
              $_mt_day = array();
            // 時間について回す. 日本語のとき.
            foreach ($TIME_LIST as $time_code => $time) {
                if ($time != '' && in_array($time_code, $res[$day_code])) {
                    // 登録されていれば, $_mt_day に登録. 
                $_mt_day[] = $time;
                }
            }
            // 時間についてまとめる.
            $times_of_the_day = join('、', $_mt_day);
              if ($times_of_the_day != '') {
                  $times_of_the_day .= '限';
              }
          }
          
          // 曜日名を付加してまとめる.
          $_mt_term[] = $day_name.$times_of_the_day;
        }
        }
      
      // 該当学期の時間をまとめる.
      if (count($terms) > 1) {
          if ($lang == 'ja') {
              $mt[] = OCWDB::getTermName($term_code, $lang) . join(' ', $_mt_term);
          } else {
              $mt[] = join(' ', $_mt_term).' ('.OCWDB::getTermName($term_code, $lang).')';
          }
      } else {
          $mt[] = join(' ', $_mt_term);
      }
    }
    
      return join(" ", $mt);
  }
  
    public static function getContents($course_id, $page_type, $contents_type)
    {
        global $db;
    
        if (!ctype_digit("$course_id") || !ctype_digit("$page_type") || !ctype_alnum("$contents_type")) {
            return false;
        }
    
        $sql = "SELECT contents.contents
      FROM pages, page_contents, contents
      WHERE pages.course_id = $course_id AND
        pages.page_type = '$page_type' AND
        pages.page_id = page_contents.page_id AND
        contents.pid = page_contents.contents_id AND
        contents.type = '$contents_type'
      ORDER BY contents.id DESC; ";
      
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $res = $sql_pdo->fetch(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          return false;
        }

        return $res[0];
    
    }
  
  // 『授業の内容』を得る
  public static function getCourseOverview($course_id, $lang='ja')
  {
      global $db;
    
    // コースIDが不正でないか。
    if (empty($course_id) || !ctype_digit("$course_id")) {
        return false;
    }
    
      if ($lang == 'ja') {
          $content_type = CT_OVERVIEW_JA;
      } else {
          $content_type = CT_OVERVIEW_EN;
      }
    
      $sql = "SELECT con.contents
                  FROM contents con
                  WHERE con.id = 
                            (SELECT max(con2.id)
                             FROM contents con2
                             WHERE con2.pid IN 
                                       (SELECT p_c.contents_id
                                        FROM page_contents p_c
                                        WHERE p_c.page_id IN
                                                 (SELECT p.page_id FROM pages p
                                                  WHERE p.course_id = $course_id) 
                                        ) AND
                                   con2.type = '$content_type'
                            )
                  ";
    
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $overview = $sql_pdo->fetch(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          return false;
        }

        return $overview[0];
    
  }
  
  // コース ID に対応するコースの教員ID と教員名・職名を配列にして返す.
  public static function getCourseInstructorInfo($course_id, $lang='ja')
  {
      global $db;
      $ocwdb = new OCWDB();
    
      if (!OCWVAR::isId($course_id) || ($lang != 'ja' && $lang != 'en')) {
          return false;
      }
    
      $sql = "SELECT instructor_id FROM course_instructor
             WHERE course_id = $course_id
             ORDER BY disp_order ASC;";
    //   $inst_id =& $db->getCol($sql);                                  // 該当する教員 ID を取得.
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $inst_id = $sql_pdo->fetch(PDO::FETCH_ASSOC);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          return false;
        }

      $res = array();
      foreach ($inst_id as $id) {
          if (!$inst_datum = $ocwdb->getInstructorNameAndPosition($id, $lang)) {
              return false;
          }
      
          $res[] = array('id' => $id, 'name' => $inst_datum['name'], 'position' => $inst_datum['position']);
      }
    
      return $res[0];
  }
 
  /**
   * 指定されたコースのビジュアルシラバス情報を返します
   * @param   $course_id  コースID
   * @param   $video_lang ビデオの言語         
   * @param   $lang       取り出す情報の言語
   * @return  array or NULL (存在しない場合) or false (エラー)
   **/
  public static function getCourseVsyllabus($course_id, $lang, $video_lang)
  {
      global $db;
 
    // 入力の確認と, 取り出すテーブルカラム名を取得.
    if (!OCWVAR::isId($course_id)) {
        return false;
    }

      if ($video_lang == 'ja') {
          $vs_cname = 'vsyllabus_id';
      } elseif ($video_lang == 'en') {
          $vs_cname = "vsyllabus_en_id";
      } else {
          return false;
      }

      if ($lang != 'ja' && $lang != 'en') {
          $lang = $video_lang;
      }
      if ($lang == 'ja') {
          $i_name = 'instructor_name';
          $i_position = 'instructor_position';
      } else {
          $i_name = 'instructor_name_e';
          $i_position = 'instructor_position_e';
      }
    
    // DB からデータを取得.
    $sql = "SELECT
              vs.movie_id, vs.vsyllabus_id, vs.url, vs.url_flv, vs.rtmp_flv, vs.movie_id,
              to_char(vs.time, 'HH24:MI:SS') AS duration,
              vs.keywords, vs.podcast_filename, 
              to_char(vs.podcast_pubdate, 'Dy, DD Mon YYYY') AS podcast_pubdate,
              i.instructor_id, i.${i_name} as instructor_name, i.{$i_position} as instructor_position
            FROM
              course c
              INNER JOIN visual_syllabus vs ON  c.${vs_cname} = vs.vsyllabus_id 
              INNER JOIN vsyllabus_instructor vi ON vs.vsyllabus_id = vi.vsyllabus_id 
              INNER JOIN instructor i ON vi.instructor_id = i.instructor_id 
            WHERE c.course_id = $course_id; ";

    //   $res =& $db->getAll($sql);
    //   if ($db->isError($res)) {
    //       return false;
    //   }
    try {
      $sql_pdo = $db->prepare($sql);
      $sql_pdo->execute();
      $res = $sql_pdo->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $e) {
        echo "データベースエラー（PDOエラー）";
        var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
          return false;
      }
      return $res;

    // 教員情報を集約し, 単純な配列に変換.
    if (!empty($res)) {
        // instructor 関係以外はそのまま使う.
      $vs_info = $res[0];

        $vs_info['instructor_names'] = array();
        foreach ($res as $item) {
            $vs_info['instructor_names'][] = array(
          'id' => $item['instructor_id'],
          'name' => $item['instructor_name'],
          'position' => $item['instructor_position']
        );
        }
        return $vs_info;
    } else {
        return null;
    }
  }
  
  //////////////////////////////
  //
  // * リスト取得
  //
  //////////////////////////////
  public static function getDepartmentList($lang='ja', $type=SCT_SHOW_OK, $child=true)
  {
      global $db;
    
      if ($lang != 'ja' && $lang != 'en' || !ctype_digit("$type")) {
          return false;
      }
    
      $sql = "SELECT * FROM
               department d
               INNER JOIN department_status ds
                 ON d.department_id = ds.department_id AND
                    ds.lang = '$lang' AND
                    ds.status <= '$type'";  // 不等号は危険？
    if (!$child) {
        $sql.="  WHERE d.department_parent_id is NULL";
    }
      $sql.= "   ORDER BY department_disp_order;";
    //   $dept_list = $db->getAll($sql);
    
    //   if (DB::isError($dept_list)) {
    //       return false;
    //   } else {
    //       return $dept_list;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $dept_list = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        return $dept_list;
              
  }
  
    public static function getDepartmentAbbrList($lang='ja', $type=SCT_SHOW_OK, $child=true)
    {
        $ocwdb = new OCWDB();

        $dept_list = $ocwdb->getDepartmentList($lang, $type, $child);
        if ($dept_list === false) {
            return false;
        } else {
            $abbr_list = array();
            if (count($dept_list) > 0) {
                foreach ($dept_list as $dept_info) {
                    $abbr_list[] = $dept_info['department_abbr'];
                }
            }
            return $abbr_list;
        }
    }
  
  // 該当コースに登録されている該当言語のページのファイルネームを返す.
  public static function getPageFilenameList($course_id, $lang)
  {
      global $db;

      $ocwvar = new OCWVAR();
    
      if (!$ocwvar->isId($course_id)) {
          return false;
      }
    
      if ($lang == 'ja') {
          $contents_type = CT_CONTENTS_JA;
      } elseif ($lang == 'en') {
          $contents_type = CT_CONTENTS_EN;
      } else {
          // 言語が不正.
      return false;
      }
    
      $sql = "SELECT COALESCE(p.filename, ptm.filename) as tplname
                FROM pages p, page_type_master ptm
                WHERE p.course_id = $course_id AND
                      ptm.page_type_code = p.page_type AND
                      EXISTS (
                        SELECT con.type FROM contents con, page_contents p_c
                          WHERE p_c.page_id = p.page_id AND
                                p_c.contents_id = con.pid AND
                                con.type = '$contents_type'
                      );";
    //   $page_filename_list = $db->getCol($sql);
    
    //   if (DB::isError($page_filename_list)) {
    //       return false;
    //   } else {
    //       return $page_filename_list;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $page_filename_list = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        return $page_filename_list;
              
  }
  
  //  該当部局の非公開、削除でないコースをすべて取り出す.
  public static function getAllCourseList($department_id=null, $lang='ja')
  {
      global $db;
    
      if ($department_id) {
          $dept_condition = "c.department_id = $department_id AND";
      } else {
          $dept_condtion = "";
      }
    
      switch ($lang) {
    case 'en':
      $column_lang = '_e';
      $separator = ', ';
      $column_semester = "c.year||' '||tcm.name_e";
      break;
      
    case 'ja':
    default:
      $lang = 'ja';
      $separator = '／';
      $column_lang = '';
      $column_semester = "c.year||'年度'||tcm.name";
    }
    
      $sql = "SELECT c.course_id, c.course_name${column_lang} as course_name,
                   ${column_semester} as course_semester,
                   d.department_id, d.department_name${column_lang} as department_name,
                   array_to_string(array(
                      SELECT
                        i.instructor_name${column_lang}
                      FROM course_instructor ci, instructor i
                      WHERE ci.course_id = c.course_id AND
                            ci.instructor_id = i.instructor_id
                      ORDER BY ci.disp_order ASC
                     ), '$separator') as instructor_name, time
            FROM course c, department d, term_code_master tcm, course_status cs, event ev,
                  ((SELECT course_id FROM course_status WHERE lang='{$lang}' AND 
                  (status='02' OR status='03' OR status='04'))
                  EXCEPT (SELECT course_id FROM course_status WHERE status='09')) AS cs02
            WHERE $dept_condition 
                  c.department_id = d.department_id AND
                  c.term = tcm.term_code AND
                  c.course_id = cs.course_id AND
                  cs.event_id = ev.event_id AND
                  cs02.course_id = c.course_id AND
                  cs.lang ='{$lang}' AND
                  (cs.status='02' OR cs.status='03' OR cs.status='04')
            ORDER BY time DESC;";
    
    //   $course_list = $db->getAll($sql);
    //   if (DB::isError($course_list)) {
    //       echo "error in db read course_list";
    //       die($course_list->getMessage());
    //       return false;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $course_list = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
                      
      return $course_list;
  }
  
  //  - コースステータスが公開可能であって, 
  //  - 公開予定のページのステータスに要修正が登録されていないもの
  public static function getIssuableCourseList($department_id=null, $lang='ja')
  {
      global $db;
    
      if ($department_id) {
          $dept_condition = "c.department_id = $department_id AND";
      } else {
          $dept_condtion = "";
      }
    
      switch ($lang) {
    case 'en':
      $column_lang = '_e';
      $separator = ', ';
      $column_semester = "c.year||' '||tcm.name_e";
      break;
      
    case 'ja':
    default:
      $lang = 'ja';
      $separator = '／';
      $column_lang = '';
      $column_semester = "c.year||'年度'||tcm.name";
    }
    
      $sql = "SELECT c.course_id, c.course_name${column_lang} as course_name,
                   ${column_semester} as course_semester,
                   d.department_id, d.department_name${column_lang} as department_name,
                   array_to_string(array(
                      SELECT
                        i.instructor_name${column_lang}
                      FROM course_instructor ci, instructor i
                      WHERE ci.course_id = c.course_id AND
                            ci.instructor_id = i.instructor_id
                      ORDER BY ci.disp_order ASC
                     ), '$separator') as instructor_name, time
            FROM course c, department d, term_code_master tcm, course_status cs, event ev,
                  ((SELECT course_id FROM course_status WHERE status='02' AND lang='{$lang}')
                  EXCEPT (SELECT course_id FROM course_status WHERE status='09')) AS cs02
            WHERE $dept_condition 
                  c.department_id = d.department_id AND
                  c.term = tcm.term_code AND
                  c.course_id = cs.course_id AND
                  cs.event_id = ev.event_id AND
                  cs02.course_id = c.course_id AND
                  cs.status='02' AND
                  cs.lang ='{$lang}'
            ORDER BY time DESC;";
    
    //   $course_list = $db->getAll($sql);
    //   if (DB::isError($course_list)) {
    //       echo "error in db read course_list";
    //       die($course_list->getMessage());
    //       return false;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $course_list = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
                      
      return $course_list;
  }
  
  // コースステータスが公開中のもの
  public static function getNowShowingCourseList($department_id=null, $lang='ja')
  {
      global $db;
    
      if ($department_id) {
          $dept_condition = "c.department_id = $department_id AND";
      } else {
          $dept_condtion = "";
      }
    
      switch ($lang) {
    case 'en':
      $column_lang = '_e';
      $separator = ', ';
      $column_semester = "c.year||' '||tcm.name_e";
      break;
      
    case 'ja':
    default:
      $lang = 'ja';
      $separator = '／';
      $column_lang = '';
      $column_semester = "c.year||'年度'||tcm.name";
    }
    
      $sql = "SELECT c.course_id, c.course_name${column_lang} as course_name,
                   ${column_semester} as course_semester,
                   d.department_id, d.department_name${column_lang} as department_name,
                   array_to_string(array(
                      SELECT
                        i.instructor_name${column_lang}
                      FROM course_instructor ci, instructor i
                      WHERE ci.course_id = c.course_id AND
                            ci.instructor_id = i.instructor_id
                      ORDER BY ci.disp_order ASC
                     ), '$separator') as instructor_name
            FROM course c, department d, term_code_master tcm
            WHERE $dept_condition 
                  c.department_id = d.department_id AND
                  c.term = tcm.term_code AND

                  EXISTS (
                      SELECT c_s.status
                       FROM course_status c_s
                       WHERE c_s.course_id = c.course_id AND
                             c_s.status = '".SCT_NOW_SHOWING."' AND
                             c_s.lang = '$lang'
                  ) AND


                  NOT EXISTS (
                      SELECT c_s.status
                       FROM  course_status c_s
                       WHERE c_s.course_id = c.course_id AND
                             ((c_s.status = '".SCT_CLOSED."' AND lang = '$lang') OR 
                               c_s.status = '".SCT_DELETED."')
                  )
            ORDER BY c.course_id;
            ";
    //   $course_list = $db->getAll($sql);
    //   if (DB::isError($course_list)) {
    //       return false;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $course_list = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
              
      return $course_list;
  }
  
  // department_id の部局に関連する，他部局開講の授業のリストを，
  // 他部局ごとにリストにして返す
  public static function getRelatedCourseList($department_id, $lang='ja', $course_status='')
  {
      global $db;
    
      switch ($lang) {
    case 'en':
      $column_lang = '_e';
      $column_semester = "c.year||' '||tcm.name_e";
      break;
      
    case 'ja':
    default:
      $lang = 'ja';
      $column_lang = '';
      $column_semester = "c.year||'年度'||tcm.name";
    }
    
    // 引数の部局に関連する授業のリスト
    $sql = "SELECT  d.department_id, d.department_name{$column_lang} as department_name, d.department_abbr, d.department_disp_order,
                    c.course_id, c.course_name{$column_lang} as course_name,
                    ${column_semester} as course_semester ";
      if ($lang=='ja') {
          // 教員名を「名大太郎／名大次郎」のように返す.
      $sql .= "    , array_to_string(array(
                      SELECT
                        i.instructor_name
                      FROM course_instructor ci, instructor i
                      WHERE ci.course_id = c.course_id AND
                            ci.instructor_id = i.instructor_id
                      ORDER BY ci.disp_order ASC
                     ), '／') as instructor_name
              ";
      }
      $sql.= " FROM   department d, course c, related_department rd, term_code_master tcm
             WHERE  d.department_id != ${department_id} AND
                    c.department_id = d.department_id AND
                    c.term = tcm.term_code AND
                    rd.course_id = c.course_id AND
                    rd.department_id = ${department_id} AND 
                    NOT EXISTS (
                      SELECT cs1.status
                      FROM course_status cs1
                      WHERE cs1.course_id = c.course_id AND
                            ((cs1.status = '".SCT_CLOSED."' AND lang = '$lang') OR 
                            cs1.status = '".SCT_DELETED."')
                     )
            ";
      if (!empty($course_status)) {
          $sql .= "      AND
                     '$course_status' IN (
                          SELECT status FROM course_status cs2
                            WHERE cs2.course_id = c.course_id AND
                                  cs2.lang = '$lang'
                      )";
      }
      $sql .= "   ORDER BY d.department_disp_order ASC, c.course_id;";
    //   $res = &$db->getAll($sql);
    //   if (DB::isError($res)) {
    //       return false;
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $res = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        
            
    // 部局ごとにまとめなおす.
    $offering_dept_list = array();
      foreach ($res as $course_data) {
          $i = $course_data['department_id'];
          if (empty($offering_dept_list[$i])) {
              $offering_dept_list[$i] =
          array(
                'department_id' => $course_data['department_id'],
                'department_name' => $course_data['department_name'],
                'department_abbr' => $course_data['department_abbr'],
                'department_disp_order' => $course_data['department_disp_order'],
                'course_list' => array()
                );
          }
          $offering_dept_list[$i]['course_list'][] =
        array(
              'course_id' => $course_data['course_id'],
              'course_name' => $course_data['course_name'],
              'instructor_name' => $course_data['instructor_name']
              );
      }
    
      return $offering_dept_list;
  }
  
  
  //////////////////////////////
  //
  // * 言語関連
  //
  //////////////////////////////
  public static function getPageLang($page_id)
  {
      global $db;
      $ocwvar = new OCWVAR();

      if (!$ocwvar->isId($page_id)) {
          return false;
      }
    
      $sql = "SELECT c.type
        FROM page_contents p_c, contents c
        WHERE  p_c.page_id = $page_id AND
          p_c.contents_id = c.pid; ";
    //   $res =& $db->getCol($sql);
    
    //   if (!DB::isError($res)) {
    //       if (in_array(CT_CONTENTS_JA, $res)) {
    //           return 'ja';
    //       } elseif (in_array(CT_CONTENTS_EN, $res)) {
    //           return 'en';
    //       }
    //   }
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $res = $sql_pdo->fetch(PDO::FETCH_ASSOC);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        if (in_array(CT_CONTENTS_JA, $res)) {
            return 'ja';
            } elseif (in_array(CT_CONTENTS_EN, $res)) {
            return 'en';
        }
            
      return false;
  }
  
  //////////////////////////////
  //
  // * ID の取得
  //
  //////////////////////////////
  public static function getPageIdByPageType($course_id, $page_type_filename, $lang)
  {
      global $db;

      $ocwdb  = new OCWDB() ;
      $ocwvar = new OCWVAR();
    //   echo "<hr>isId<hr>" ;
    // print_r( !$ocwvar->isId($course_id) );
    // echo "<hr>getPageFilenameList<hr>" ;
    // print_r( $ocwdb->getPageFilenameList($course_id, $lang) );

    //   if (!$ocwvar->isId($course_id)
    //    ||($lang != 'ja' && $lang != 'en')
    //    || !in_array($page_type_filename, $ocwdb->getPageFilenameList($course_id, $lang))) {
    //        echo "<hr>false<hr>" ;
    //       return false;
    //   }
    
      $sql = "SELECT p.page_id FROM pages p, page_type_master ptm
             WHERE p.course_id=$course_id AND
                   p.page_type = ptm.page_type_code AND
                   ptm.filename = '$page_type_filename';";
    //   $ids = $db->getCol($sql);
    // print_r($sql);
      try {
        $sql_pdo = $db->prepare($sql);
        $sql_pdo->execute();
        $ids = $sql_pdo->fetchAll(PDO::FETCH_BOTH);
      }catch(PDOException $e) {
          echo "データベースエラー（PDOエラー）";
          // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
            return false;
        }
        // echo "<hr> ids: <br><br>".$ids."<br>" ;  
        // print_r($ids) ;
        // echo "getPageLang<hr>";
        // print_r($ocwdb->getPageLang($id));
    if (!empty($ids)) {
          foreach ($ids[0] as $id) {
              // 候補の中から該当する言語を持つページ ID を返す.
            //   echo "id ; ".$id;
        if ($ocwdb->getPageLang($id) == $lang) {
            return $id;
            }
          }
      }
            
    // 見つからなかった.
    return false;
  }
  
  
    public static function getCourseIdByPageId($page_id)
    {
        global $db;
        $ocwvar = new OCWVAR();

    // ページ ID が不正でないか.
    if (!$ocwvar->isId($page_id)) {
        return false;
    }
    
        $sql = "SELECT course_id FROM pages WHERE page_id = $page_id;";
        // $course_id = $db->getOne($sql);
    
        // if (DB::isError($course_id)) {
        //     return false;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $course_id = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            
        return $course_id[0];
    }
  
    public static function getDepartmentIdByCourseId($course_id)
    {
        global $db;
    
        if (!ctype_digit("$course_id")) {
            return false;
        }
    
        $sql = "SELECT department_id FROM course WHERE course_id = $course_id";
        // $department_id = $db->getOne($sql);
    
        // if (DB::isError($department_id)) {
        //     return false;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $department_id = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            
        return $department_id[0];
    }
  
    public static function getDepartmentAbbrByCourseID($course_id)
    {
        global $db;
        if (!ctype_digit("$course_id")) {
            return false;
        }
    
        $sql = "SELECT d.department_abbr 
      FROM course c, department d
      WHERE c.course_id = $course_id AND
                   c.department_id = d.department_id";
        // $department_abbr = $db->getOne($sql);
    
        // if (DB::isError($department_abbr)) {
        //     return false;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $department_abbr = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            
        return $department_abbr[0];
    }
  
    public static function getDepartmentIdByDepartmentAbbr($department_abbr)
    {
        global $db;
    
        $sql = "SELECT d.department_id
      FROM department d
      WHERE d.department_abbr = '${department_abbr}'";
    
        // $department_id = $db->getOne($sql);
        // if (DB::isError($department_id)) {
        //     return false;
        // }
        try {
            $sql_pdo = $db->prepare($sql);
            $sql_pdo->execute();
            $department_id = $sql_pdo->fetch(PDO::FETCH_BOTH);
          }catch(PDOException $e) {
              echo "データベースエラー（PDOエラー）";
              // var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
                return false;
            }
            
        return $department_id[0];
    }
  
  //////////////////////////////
  //
  // * ステータス関連
  //
  //////////////////////////////

  // ページステータスを設定する.
  //  - $flg は true or false.
  //  - トランザクション管理は呼び出し側で行うこと！
  public static function setPageStatus($page_id, $status_code, $flg)
  {
      global $db;
    
      if (!ctype_digit("$page_id") || !ctype_digit("$status_code") || !is_bool($flg)) {
          return false;
      }
    
    // page_id から page_nameを得る
    $page_name = OCWDB::getPageName($page_id);
    
    // status から status の名称を得る
    $status_name = OCWDB::getStatusName($status_code);
    
    // 重複登録のチェック用: 現在のステータスリスト
    $sql = "SELECT p_s.status FROM page_status p_s WHERE p_s.page_id = $page_id;";
      $status_list =& $db->getCol($sql);
      if (DB::isError($status_list)) {
          return false;
      }
    
    // イベント登録
    $description = "${page_name}: ${status_name}を" . (($flg) ? '有効に' : '無効に');
      if (!OCWDB::setOkEvent(ET_PAGE_STATUS_CHANGED, $description)
      || !OCWDB::setEventRelation('', RIT_PAGE_ID, $page_id)
    ) {
          return false;
      }
    
    // ページステータス変更
    if ($flg) {
        // 登録時
      if (!in_array($status_code, $status_list)) {
          $sql = "INSERT INTO page_status (page_id, status, event_id)
            VALUES ($page_id, '$status_code', currval('event_id_seq'));";
          $res =& $db->query($sql);
      }
    } else {
        // 削除時
      if (in_array($status_code, $status_list)) {
          $sql = "DELETE FROM page_status
           WHERE page_id = $page_id AND status = '$status_code';";
          $res =& $db->query($sql);
      }
    }
    
      if (DB::isError($res)) {
          return false;
      } else {
          return true;
      }
  }
  
  // ページステータスが設定されているか調べる.
  public static function isSetPageStatus($page_id, $status_code)
  {
      global $db;
    
      if (!OCWVAR::isId($page_id) || !ctype_digit("$status_code")) {
          return false;
      }
    
      $sql = "SELECT * FROM page_status
       WHERE page_id = $page_id AND status = '$status_code';";
      $res =& $db->query($sql);
    
      if (!DB::isError($res) && $res->numRows() > 0) {
          return true;
      } else {
          return false;
      }
  }
  
  // ページステータスの設定されたページがあるかを調べる.
  public static function existSetPageStatus($course_id, $status_code)
  {
      global $db;
    
      if (!OCWVAR::isId($course_id) || !ctype_digit("$status_code")) {
          return false;
      }
    
      $sql = "SELECT p.page_id
      FROM pages p
      WHERE
        p.course_id = $course_id AND
        EXISTS     (SELECT p_s.status FROM page_status p_s
                     WHERE p_s.page_id = p.page_id AND p_s.status = '$status_code') AND
        NOT EXISTS (SELECT p_s.status FROM page_status p_s
                     WHERE p_s.page_id = p.page_id AND p_s.status = '".SCT_CLOSED."');";
      $res =& $db->query($sql);
    
      if (DB::isError($res) || $res->numRows() == 0) {
          return false;
      } else {
          return true;
      }
  }
  
  // コースステータスを設定する.
  //  - $flg は true or false.
  //  - トランザクション管理は呼び出し側で行うこと！
  public static function setCourseStatus($course_id, $status_code, $flg, $lang='ja')
  {
      global $db;
    
      if (!OCWVAR::isId($course_id) || !ctype_digit("$status_code") || !is_bool($flg) || ($lang != 'ja' && $lang != 'en')) {
          return false;
      }
    
    // course_id から course_nameを得る
    $course_name = OCWDB::getCourseName($course_id);
    
    // status から status の名称を得る
    $status_name = OCWDB::getStatusName($status_code);
    
    // 重複登録のチェック用: 現在のステータスリスト
    $sql = "SELECT c_s.status FROM course_status c_s WHERE c_s.course_id = $course_id AND lang = '$lang';";
      $status_list =& $db->getCol($sql);
    
      if (DB::isError($status_list)) {
          return false;
      }
    
    // イベント登録
    $description = "${course_name} (${lang}): ${status_name}を" . (($flg) ? '有効に' : '無効に');
      if (!OCWDB::setOkEvent(ET_COURSE_STATUS_CHANGED, $description)
      || !OCWDB::setEventRelation('', RIT_COURSE_ID, $course_id)
    ) {
          return false;
      }

    // コースステータス変更
    if ($flg) {
        // 登録時
      if (!in_array($status_code, $status_list)) {
          $sql = "INSERT INTO course_status (course_id, lang, status, event_id)
            VALUES ($course_id, '$lang', '$status_code', currval('event_id_seq'));";
          $res =& $db->query($sql);
      }
    } else {
        // 削除時
      if (in_array($status_code, $status_list)) {
          $sql = "DELETE FROM course_status
           WHERE course_id = $course_id AND lang = '$lang' AND status = '$status_code';";
          $res =& $db->query($sql);
      }
    }
    
      if (DB::isError($res)) {
          return false;
      } else {
          return true;
      }
  }
  
  // コースステータスが設定されているか調べる.
  public static function isSetCourseStatus($course_id, $status_code, $lang='ja')
  {
      global $db;
    
      if (!OCWVAR::isId($course_id) || !ctype_digit("$status_code") || ($lang != 'ja' && $lang != 'en')) {
          return false;
      }
    
      $sql = "SELECT * FROM course_status
       WHERE course_id = $course_id AND status = '$status_code' AND lang='$lang';";
      $res =& $db->query($sql);
    
      if (!DB::isError($res) && $res->numRows() > 0) {
          return true;
      } else {
          return false;
      }
  }
  
  // 公開可能なコースに対して、コースステータスをチェック中に戻し,
  // 問題のあるページを編集中・チェック中に設定する.
  //  * トランザクション管理は呼び出し側で行うこと！
  public static function backToChecking($course_id, $lang='ja')
  {
      global $db;
    
      if (!OCWVAR::isId($course_id) || ($lang != 'ja' && $lang != 'en')) {
          return false;
      }
    
    // 公開可能なコースかどうか.
    if (!OCWDB::isSetCourseStatus($course_id, SCT_SHOW_OK, $lang)) {
        return false;
    }
    
    // コースステータスを変更.
    if (!OCWDB::setCourseStatus($course_id, SCT_SHOW_OK, false, $lang)) {
        return false;
    }
      if (!OCWDB::setCourseStatus($course_id, SCT_CHECKING, true, $lang)) {
          return false;
      }
      if (!OCWDB::setCourseStatus($course_id, SCT_EDITABLE, true, $lang)) {
          return false;
      }
    
    // 問題のあったページ(非公開でないページで, 公開可能以外のステータスが設定されていた)には, 
    // 編集中・チェック中を設定
    $sql = "SELECT p.page_id FROM pages p
            WHERE p.course_id = $course_id AND
                  EXISTS (SELECT p_s.status FROM page_status p_s WHERE p_s.page_id = p.page_id) AND
                  NOT EXISTS (SELECT p_s.status FROM page_status p_s
                         WHERE p_s.page_id = p.page_id AND p_s.status = '".SCT_CLOSED."');";
      $row =& $db->getCol($sql);
    
      foreach ($row as $page_id) {
          if (OCWDB::getPageLang($page_id) == $lang) {
              if (!OCWDB::setPageStatus($page_id, SCT_EDITING, true)) {
                  return false;
              }
              if (!OCWDB::setPageStatus($page_id, SCT_CHECKING, true)) {
                  return false;
              }
          }
      }
    
      return true;
  }
  
  // 編集時の各段階が進行中であるかどうかを page status から判定する.
  public static function isWorking($course_id, $lang='ja')
  {
      global $db;
    
      if (!OCWVAR::isId($course_id) || ($lang != 'ja' && $lang != 'en')) {
          return true;
      }
    
      $contents_type = ($lang == 'ja') ? CT_CONTENTS_JA : CT_CONTENTS_EN;
    
    // ページステータスの設定されているページで, 「非公開」の設定されていないものを取り出す.
    $sql = "SELECT p.page_id                                                 ";
      $sql.= " FROM pages p                                                    ";
      $sql.= "   INNER JOIN page_contents pc USING (page_id)                   ";
      $sql.= "   INNER JOIN contents c ON (pc.contents_id = c.id)              ";
      $sql.= "   NATURAL INNER JOIN                                            ";
      $sql.= "     ((SELECT page_id FROM page_status) EXCEPT                   ";
      $sql.= "      (SELECT page_id FROM page_status WHERE status IN ('".SCT_CLOSED."') )  ";
      $sql.= "     ) AS working_pages                                          ";
      $sql.= "  WHERE course_id = $course_id AND c.type = '$contents_type';    ";
      $res =& $db->query($sql);
    
      if (DB::isError($res) || $res->numRows() > 0) {
          // 問い合わせ失敗 (本来は例外処理)
      // または, 編集中のページあり.
      return true;
      }

      $vsyllabus_info = OCWDB::getCourseVsyllabus($course_id, $lang, $lang);
      $vsyllabus_id = $vsyllabus_info['vsyllabus_id'];
      if ($vsyllabus_id && !OCWDB::getVsyllabusFileName($vsyllabus_id)) {
          // 1分間授業紹介画像が未登録. 編集中.
      return true;
      }

    // 編集完了.
    return false;
  }
  
  // 部局の公開ステータスを設定する. cf. setCourseStatus
  //  - $flg は true or false.
  //  - トランザクション管理は呼び出し側で行うこと！
  public static function setDepartmentStatus($department_id, $lang, $status_code, $flg)
  {
      global $db;
    
      if (!OCWVAR::isId($department_id) || ($lang != 'ja' && $lang != 'en') || !ctype_digit("$status_code") || !is_bool($flg)) {
          return false;
      }
    
    // department_id から department_nameを得る
    $department_name = OCWDB::getDepartmentName($department_id);
    
    // status から status の名称を得る
    $status_name = OCWDB::getStatusName($status_code);
    
    // 重複登録のチェック用: 現在のステータスリスト
    $sql = "SELECT d_s.status FROM department_status d_s WHERE d_s.department_id = $department_id AND d_s.lang='$lang';";
      $status_list =& $db->getCol($sql);
    
      if (DB::isError($status_list)) {
          return false;
      }
    
    // イベント登録
    $description = "${department_name}: ${status_name}を" . (($flg) ? '有効に' : '無効に');
      if (!OCWDB::setOkEvent(ET_DEPARTMENT_STATUS_CHANGED, $description)
      || !OCWDB::setEventRelation('', RIT_DEPARTMENT_ID, $department_id)
    ) {
          return false;
      }
    
    // ステータス変更
    if ($flg) {
        // 登録時
      if (!in_array($status_code, $status_list)) {
          $sql = "INSERT INTO department_status (department_id, lang, status, event_id)
            VALUES ($department_id, '$lang', '$status_code', currval('event_id_seq'));";
          $res =& $db->query($sql);
      }
    } else {
        // 削除時
      if (in_array($status_code, $status_list)) {
          $sql = "DELETE FROM department_status
           WHERE department_id = $department_id AND lang = '$lang' AND status = '$status_code';";
          $res =& $db->query($sql);
      }
    }
    
      if (DB::isError($res)) {
          return false;
      } else {
          return true;
      }
  }
  

  // コースについて講義資料が用意されているか調べる。
  public static function existLectNotes($course_id, $lang='ja')
  {
      global $db;

    // コース ID が不正でないか.
    if (empty($course_id) || !ctype_digit("$course_id")) {
        return false;
    }

      $cont_type = ($lang == 'ja') ? CT_CONTENTS_JA : CT_CONTENTS_EN;

      $sql =<<<EOD
SELECT 
  p.page_id
FROM
  pages AS p 
  LEFT JOIN page_status AS ps ON ps.page_id = p.page_id 
  LEFT JOIN page_contents AS pc ON pc.page_id = p.page_id
  LEFT JOIN contents AS c ON c.id = pc.contents_id
WHERE 
  p.page_type IN ('54', '55', '58', '73') 
  AND ps.status IS NULL
  AND p.course_id = '$course_id' 
  AND c.type = '$cont_type';
EOD;
      $exist_notes = $db->getOne($sql);
      if (DB::isError($exist_notes)) {
          return false;
      }

      if (!empty($exist_notes)) {
          return t;
      } else {
          return f;
      }
  }

  // コースの公開日を調べる
  public static function whenReleased($course_id, $lang)
  {
      global $db;

      if (!OCWVAR::isId($course_id) || ($lang != 'ja' && $lang != 'en')) {
          return false;
      }

      $sql = "SELECT time FROM course_status NATURAL JOIN event
              WHERE course_id=${course_id} AND lang='${lang}' AND status='".SCT_NOW_SHOWING."';";
      $time = $db->getOne($sql);

      if (!DB::isError($time) && !is_null($time)) {
          return $time;
      } else {
          return false;
      }
  }

  //////////////////////////////
  //
  // * ファイル関連
  //
  //////////////////////////////
  public static function getCourseIdByFileGid($file_gid)
  {
      global $db;
    
    // ページ ID が不正でないか.
    if (!ctype_digit("$file_gid")) {
        return false;
    }
    
      $sql = "SELECT relation_id FROM file_group WHERE id = $file_gid AND relation_type = '".RIT_COURSE_ID."';";
      $course_id = $db->getOne($sql);
    
      if (DB::isError($course_id) || !$course_id) {
          return false;
      }
      return $course_id;
  }
  
  // ファイル ID からコース ID を得る.
  public static function getCourseIdByFileId($file_id)
  {
      global $db;
    
      return OCWDB::getCourseIdByFileGid(OCWDB::getFileGidByFileId($file_id));
  }
  
  // ファイル ID から拡張子を得る.
  public static function getExtensionByFileId($file_id)
  {
      global $db;
    
      if (!OCWVAR::isId($file_id)) {
          return false;
      }
    
      $sql = "SELECT mtm.extension FROM file f, mime_type_master mtm
                 WHERE f.id = $file_id AND
                       mtm.id = f.mime_type;";
      $extension = $db->getOne($sql);
    
      if (!DB::isError($extension)) {
          return $extension;
      } else {
          return false;
      }
  }
  
  // ファイル名を得る.
  public static function getFileName($file_gid)
  {
      global $db;
    
    // ファイル ID が不正でないか.
    if (!ctype_digit("$file_gid")) {
        return false;
    }
    
      $sql=  "SELECT name
        FROM file_group
       WHERE id = $file_gid
       ; ";
      $file_name = $db->getOne($sql);
    
      if (DB::isError($file_name)) {
          return false;
      }
      return $file_name;
  }
  
  // ビジュアルシラバス画像のファイル名を得る.
  public static function getVsyllabusFileName($vsyllabus_id)
  {
      define('FILE_NAME_PREFIX', 'vsyllabus_');
      define('VSYLLABUS_FILEDIR', FILEDIR. '/vsyllabus_pic/');

    // ファイル ID が不正でないか.
    if (!ctype_digit("$vsyllabus_id")) {
        return false;
    }
    
      $filename = FILE_NAME_PREFIX . $vsyllabus_id . ".jpg";
      if (is_file(VSYLLABUS_FILEDIR . $filename)) {
          return $filename;
      } else {
          return null;
      }
  }

  // 指定された file group id に対して, 現在有効な file id を返す.
  public static function getCurrentFileId($file_gid)
  {
      global $db;
    
      if (!ctype_digit("$file_gid")) {
          return false;
      }
    
    // ひとまず, 与えられた gid をもち, id が最大のものが有効とする.
    $sql = "SELECT max(id) FROM file
        WHERE gid = $file_gid;";
      $id = $db->getOne($sql);
    
      if (DB::isError($id)) {
          return false;
      }
    
      return $id;
  }
  
  // ファイル ID からファイルグループ ID を得る.
  public static function getFileGidByFileId($file_id)
  {
      global $db;
    
      if (!ctype_digit("$file_id")) {
          return false;
      }
    
      $sql = "SELECT gid FROM file WHERE id=$file_id;";
      $res = $db->getOne($sql);
    
      if (!DB::isError($res)) {
          return $res;
      } else {
          return false;
      }
  }
  
  
  // 指定された file group の del_flg を上げ下げする.
  //   - $flg には TRUE または FALSE を入れる.
  //   - トランザクション管理は呼び出し側ですること.
  public static function chflgFileGroup($file_gid, $flg)
  {
      global $db;
    
      if (!ctype_digit("$file_gid") || !is_bool($flg)) {
          return false;
      }
    
    // del_flg を上げ下げする.
    $data = array(
                  'del_flg' => ($flg) ? 't' : 'f'
                  );
      $res = $db->autoExecute('file_group', $data, DB_AUTOQUERY_UPDATE, "id = $file_gid");
      if ($db->isError($res)) {
          return false;
      }
    
    // イベント登録
    $type = ($flg) ? ET_FILE_DELETED : ET_FILE_ROLLBACKED;
      $description = 'ファイル('.OCWDB::getFileName($file_gid).')を消去しました';
      if (!OCWDB::setOkEvent($type, $description)
      || !OCWDB::setEventRelation('', RIT_FILE_GROUP_ID, $file_gid)
    ) {
          return false;
      }
    
      return true;
  }
  
  
  // ファイルアップロード時に、mime型を表す文字列からmimetypeIDを得る
  public static function getMimeTypeId($mime_type, $extension=null)
  {
      global $db;
    
      $sql = "SELECT mime_type_master.id
                  FROM mime_type_master
                  WHERE mime_type_master.mime_type = '$mime_type'
                  ";
      if (!empty($extension)) {
          $sql .=  "AND mime_type_master.extension = '$extension'
                      ";
      }
      $res =& $db->getAll($sql);
    
      if (DB::isError($res)) {
          return false;
      }
      return $res;
  }
  
  // ファイルバックアップ用の、重複しないファイル名を探す関数
  public static function getBakName($filepath)
  {
      $i = 0;
      $no = sprintf("%03d", $i);
    
      while (file_exists($filepath.".bak".$no)) {
          $i++;
          $no = sprintf("%03d", $i);
          if ($i > 999) {
              die("<p>バックアップした同名のファイルの数が999個を超えたので、バックアップ用のファイルを保存できません。別の名前を付けて下さい。</body></html>");
          }
      }
      return basename($filepath.".bak".$no);
  }
  
  // コースの看板画像のファイルgroupIDを返す
  public static function getIndexImageGid($course_id)
  {
      global $db;
    
      $sql = "SELECT img
                  FROM course c
                  WHERE c.course_id = $course_id       
                  ";
      $file_gid =& $db->getOne($sql);
      if (DB::isError($file_gid)) {
          return false;
      }
      return $file_gid;
  }
  
  //////////////////////////////
  //
  // * イベント関連
  //
  //////////////////////////////
  // コースに関連したイベントリスト（event_id降順）を返す
  // $start と $disp_num が指定されているときは、
  // $start 件目から $disp_num 件のみ返す
  public static function getEventListInCourse($course_id, $start=null, $disp_num=null)
  {
      global $db;
    
      $sql = "SELECT DISTINCT e.event_id, e.type as event_type, e.description as detail, e.time, 
                 e_r.relation_id, e_r.relation_type, etm.description as cat, u.name as name
      FROM event e, event_relation e_r, event_type_master etm, pages p, extended_user_info u
        -- コースに関係のあるページのイベント(ページ閲覧(type901)は除く)
      WHERE  p.course_id = $course_id AND
                          e_r.relation_type = '02' AND
                           e_r.relation_id = p.page_id AND
                           e_r.event_id = e.event_id AND
                          e.type = etm.event_type AND
                          e.type <> '901' AND
                           u.id = e.publisher
    UNION

    SELECT DISTINCT e.event_id, e.type as event_type, e.description as detail, e.time, 
                 e_r.relation_id, e_r.relation_type, etm.description as cat, u.name as name
          FROM event e, event_relation e_r, event_type_master etm, extended_user_info u      
                -- コースのイベント
                WHERE  e_r.relation_type = '01' AND
                       e_r.relation_id = $course_id AND
                       e_r.event_id = e.event_id AND
                       e.type = etm.event_type AND
                       u.id = e.publisher

    UNION

    SELECT DISTINCT e.event_id, e.type as event_type, e.description as detail, e.time, 
             e_r.relation_id, e_r.relation_type, etm.description as cat, u.name as name
          FROM event e, event_relation e_r, event_type_master etm, file_group f_g, extended_user_info u      
           -- コースに関係のあるファイル関連のイベント
                WHERE  f_g.relation_type = '01' AND
                       f_g.relation_id = $course_id AND
                       e_r.relation_type = '07' AND 
                       e_r.relation_id = f_g.id AND
                       e_r.event_id = e.event_id AND
                       e.type = etm.event_type AND
                       u.id = e.publisher
    ORDER BY event_id DESC";
      if ($start && $disp_num) {
          $sql .= " LIMIT $disp_num OFFSET $start";
          $event_list =& $db->getAll($sql);
      //$event_list =& $db->limitQuery($sql, $start, $disp_num); 
      } else {
          $event_list =& $db->getAll($sql);
      }
      if (DB::isError($event_list)) {
          return false;
      }
      return $event_list;
  }
  
  // ページ($page_id)に対するコメントで、
  // ユーザ($user_id)が未見のものの数を返す
  public static function getNotSeenEventsNumber($page_id, $user_id)
  {
      global $db;
    
    // $user_id の、最終のページ閲覧イベントのIDを得る
    $sql = "SELECT max(e.event_id)  
     FROM event e, event_relation e_r
     WHERE   e.type = '901' AND
      e.event_id = e_r.event_id AND
      e_r.relation_type = '02' AND
      e_r.relation_id = $page_id AND
      e.publisher = '$user_id'";
      $last_browsed_event_id =& $db->getOne($sql);
      if (DB::isError($last_browsed_event_id)) {
          return false;
      }
    // まだ1度もそのページを見たことがない場合、
    // 上の SQL を実行すると NULL が返ってきて
    // 次に行う event_id の比較に都合が悪いので 0 を代入する
    if (empty($last_browsed_event_id)) {
        $last_browsed_event_id = 0;
    }
    
    // $last_browsed_event_id よりもevent_idが若い
    // コメントイベントの数を得る
    $sql = "SELECT count(*)
      FROM event e, event_relation e_r  
      WHERE  e.type = '801' AND
      e.event_id = e_r.event_id AND
      e_r.relation_type = '02' AND
      e_r.relation_id = $page_id AND
      e.event_id > $last_browsed_event_id ";
      $comm_num =& $db->getOne($sql);
    
      if (DB::isError($comm_num)) {
          return false;
      } else {
          return $comm_num;
      }
  }


  // event を登録する。
  public static function setEvent($result, $type, $description)
  {
      global $db;

      if (empty($_SESSION['userid'])
      || !OCWVAR::isId($result)
      || !OCWVAR::isId($type)
    ) {
          return false;
      }

      $event_data = array(
      'publisher' => $_SESSION['userid'],
      'result' => $result,
      'type' => $type,
      'description' => $description
    );
      $res = $db->autoExecute("event", $event_data, DB_AUTOQUERY_INSERT);

      if (!DB::isError($res)) {
          return true;
      } else {
          return false;
      }
  }

  // 成功した event を登録する。
  public static function setOkEvent($type, $description)
  {
      return OCWDB::setEvent(ERT_OK, $type, $description);
  }

  // エラーの event を登録する。
  public static function setErrorEvent($type, $description)
  {
      return OCWDB::setEvent(ERT_ERROR, $type, $description);
  }

  // event_relation を登録する。
  // $event_id が空の場合は直近のイベントの ID を用いる。
  public static function setEventRelation($event_id, $relation_type, $relation_id)
  {
      global $db;

      if (!ctype_digit($event_id)) {
          if (empty($event_id)) {
              $event_id = "currval('event_id_seq')";
          } else {
              return false;
          }
      }

      if (!OCWVAR::isId($relation_type)
      || !OCWVAR::isId($relation_id)
    ) {
          return false;
      }


      $sql = "INSERT INTO event_relation (event_id, relation_type, relation_id)
              VALUES (${event_id}, '${relation_type}', ${relation_id}); ";
      $res =& $db->query($sql);

      if (!DB::isError($res)) {
          return true;
      } else {
          return false;
      }
  }
}
