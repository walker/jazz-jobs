<?php

if(!class_exists('Jazz')) {
    class Jazz {
        private $apikey = null;

        private $application_errors = array();

        # admin
        function __construct() {
          $resumator_api_key = get_option('resumator_api_key');
          $this->apikey = '?apikey='.$resumator_api_key;
        }

        static function addMenuItem() {
            add_options_page(
                'Jazz',
                'Jazz',
                'manage_options',
                'resumator.php',
                'resumator_admin_form'
            );
        }

        public function adminForm() {
            if(isset($_POST) && !empty($_POST)) {
                // Form data sent
                $resumator_api_key = $_POST['resumator_api_key'];
                update_option('resumator_api_key', $resumator_api_key);

                $resumator_url_path = $_POST['resumator_url_path'];
                update_option('resumator_url_path', $resumator_url_path);

                $resumator_job_bank = $_POST['resumator_job_bank'];
                update_option('resumator_job_bank_id', $resumator_job_bank);

                echo '<div class="updated"><p><strong>'.__('Options saved.').'</strong></p></div>';
            } else {
                // Normal page display
                $resumator_api_key = get_option('resumator_api_key');
                $resumator_url_path = get_option('resumator_url_path');
                $resumator_job_bank = get_option('resumator_job_bank_id');
            }

            $all_jobs = $this->get_all_jobs();
            $jobs_dd = array();
            foreach($all_jobs->jobs as $j) {
              $jobs_dd[$j->id] = $j->title;
            }

            // $my_pages = get_pages('parent=0');
            // $r_pages = array();
            // foreach($my_pages as $one_page) {
            //     $r_pages[] = array(
            //         'id' => $one_page->ID,
            //         'title' => $one_page->post_title
            //     );
            // }

            require_once( JAZZ_PLUGIN_DIR . DS . 'lib' . DS . 'admin_form.php');
        }
        # shortcode

        public function get_all_jobs() {
          $return = (object) array();
          // var_dump(JAZZ_API_URL.'jobs'.$this->apikey);
          $result = wp_remote_get(JAZZ_API_URL.'jobs/status/open'.$this->apikey, array('timeout' => 20));
          if(isset($result['body']) && isset($result['headers']['content-type']) && $result['headers']['content-type']=='application/json') {
            try {
              $return->jobs = json_decode($result['body'], true);
              foreach($return->jobs as $k => $v) {
                $return->jobs[$k] = (object) $v;
              }
            } catch(Exception $e) {
              // We should output this one. A 500.
              // error decoding
            }
            return $return;
          }
        }

        # auto-urls
        function autOutput($request) {
            global $wpdb;
            // $resumatorURLPath_pageID = get_option('resumator_url_path');
            $respath = get_option('resumator_url_path');

            // $rupp = $wpdb->get_results( $wpdb->prepare( 'SELECT post_name FROM '.$wpdb->prefix.'posts WHERE ID=%d', $resumatorURLPath_pageID ) );
            // if(count($rupp)===1) {
            //     $respath = '/'.$rupp[0]->post_name.'/';
            // }
            $request_path = '/'.$request->request;

            if($request_path.'/'==$respath) {
                $request_path .= '/';
            }

            if((isset($respath) && ($respath!='/' || ($respath=='/' && (strpos($request_path, '/job')===0 || strpos($request_path, '/submit-resume')===0))) && strpos($request_path, $respath)===0) || (isset($respath) && $respath=='/' && $request_path=='/')) {
                // Remove the initial path
                $path_length = strlen($request_path);
                $resumator_length = strlen($respath);
                $neg = 0 - ( $path_length - $resumator_length );
                $path = substr($request_path, $neg);
                // Get the parse_request argument and parse out for processing
                if($request_path==$respath) {
                    $call = array();
                } else {
                    $call = explode('/', $path);
                }

                // Rebuild the request call path // we can't and probably shouldn't use this here right
                // $call_path = implode('/', $path_comps);

                // Determine which call to process and start processing.
                // If an invalid call was made, perform error handling and return a response to the client!

                try {
                    // if(count($call)<1) { $call[] = 'jobs'; }
                    switch ($call[0]) {
                      case 'submit-resume':
                        $resumator_job_bank = get_option('resumator_job_bank_id');
                        $resumator_dom_id = $resumator_dom_class = 'applicant';
                        switch($_SERVER['REQUEST_METHOD']) {
                          case 'GET':
                          case 'POST':
                            $resumator = (object) array();
                            $result = wp_remote_get(JAZZ_API_URL.'jobs/'.$resumator_job_bank.$this->apikey, array('timeout' => 20));
                            if(isset($result['body']) && isset($result['headers']['content-type']) && $result['headers']['content-type']=='application/json') {
                              try {
                                $resumator->job = json_decode($result['body']);
                                unset($resumator->job->job_applicants);

                                $resumator->job->apply_now = $respath.'job/'.$resumator_job_bank.'/apply';
                                $resumator->apply_job = true;

                                $resumator->user_form = $this->renderUserForm(null, $resumator->job->id);
                                $resumator->user_form .= $this->renderQuestionnaireForm($resumator->job->questionnaire);
                                $resumator->user_form .= $this->close_form();

                                // We should attach/render to template
                                if( locate_template( array( 'jazz-apply-page.php') ) != '' ) {
                                    require_once( locate_template( array( 'jazz-apply-page.php' ), false, false ) );
                                } else {
                                    require_once(JAZZ_PLUGIN_DIR.DS.'template'.DS.'default.php');
                                }
                                exit();
                              } catch(Exception $e) {
                                // We should output this one. A 500.
                                // error decoding
                                // Nah, let's let it fall through to 404.
                              }
                            }
                          break;
                        }
                      break;
                      case 'job':
                        switch($_SERVER['REQUEST_METHOD']) {
                          case 'GET':
                          case 'POST':
                            $resumator = (object) array();
                            if(isset($call[1])) {
                              $resumator->dom_id = 'job-'.$call[1];
                              $resumator->dom_class = 'job';
                              $job_id = trim($call[1]);
                              if(!empty($call[1])) {
                                $result = wp_remote_get(JAZZ_API_URL.'jobs/'.$call[1].$this->apikey, array('timeout' => 20));
                                if(isset($result['body']) && isset($result['headers']['content-type']) && $result['headers']['content-type']=='application/json') {
                                  try {
                                    $resumator->job = json_decode($result['body']);
                                  } catch(Exception $e) {
                                    // We should output this one. A 500.
                                    // error decoding
                                  }
                                  // echo '<pre>';
                                  // var_dump($resumator->job);
                                  // echo '</pre>';
                                  // exit();
                                  unset($resumator->job->job_applicants);
                                  // var_dump($resumator->job);
                                  // exit();
                                  if(!isset($call[2]) || (isset($call[2]) && $call[2]!='apply')) {
                                    $resumator->job->apply_now = $respath.'job/'.$resumator->job->id.'/apply';
                                    
                                    // We should attach/render to template
                                    if( locate_template( array( 'jazz-page.php') ) != '' ) {
                                      require_once( locate_template( array( 'jazz-page.php' ), false, false ) );
                                    } else {
                                      require_once(JAZZ_PLUGIN_DIR.DS.'template'.DS.'default.php');
                                    }
                                    exit();
                                  } else if(isset($call[2]) && $call[2]=='apply') {
                                    $resumator->job->view = $respath.'job/'.$resumator->job->id;
                                    $resumator->dom_id = 'job-'.$call[1].'-apply';
                                    $resumator->dom_class = 'apply';
                                    $resumator->apply_job = true;

                                    // Application submission
                                    if(isset($_POST) && !empty($_POST)) {
                                      // Validate
                                      // var_dump($_POST);
                                      // var_dump($_FILES);
                                      // exit();
                                      if($this->validate_application($resumator->job->id, $_POST)) {
                                        // Submit /applicants
                                        $applicant_id = $this->submit_application($resumator->job->id, $_POST);
                                        if(!$applicant_id) {
                                          //there was an error, let it render below
                                        } else {
                                          // Get returned applicant_id and submit questionnaire_answers
                                          $this->submit_questionnaire_answers($applicant_id, $resumator->job->id, $resumator->job->questionnaire, $_POST['questionnaire']);
                                          // redirect to /thank-you page (or whatever)
                                          wp_redirect('/thank-you');
                                        }
                                      }
                                    }

                                    // use info from above to render/attach the application here
                                    // The user currently has an applicant_id, add "Submit application as..." html here.

                                    $resumator->user_form = $this->renderUserForm(null, $resumator->job->id);

                                    // Questionnaire
                                    // 1 on question_status == show active questions only
                                    // /v1/questionnaire_questions/questionnaire_id/'.$questionnaire_id.'/question_status/1
                                    $resumator->user_form .= $this->renderQuestionnaireForm($resumator->job->questionnaire);

                                    $resumator->user_form .= $this->close_form();

                                    // We should attach/render to template
                                    if( locate_template( array( 'jazz-apply-page.php') ) != '' ) {
                                      require_once( locate_template( array( 'jazz-apply-page.php' ), false, false ) );
                                    } else {
                                      require_once(JAZZ_PLUGIN_DIR.DS.'template'.DS.'default.php');
                                    }
                                    exit();
                                  }
                                }
                              }
                            }
                          break;
                        }
                      break;
                      case 'jobs':
                        switch($_SERVER['REQUEST_METHOD']) {
                          case 'GET':
                            if(count($call)===1) {
                              // TODO: Use get_all_jobs() here.

                              $resumator_job_bank = get_option('resumator_job_bank_id');

                              $resumator = (object) array();
                              $resumator->dom_id = $resumator->dom_class = 'jobs';
                              // var_dump(JAZZ_API_URL.'jobs'.$this->apikey);
                              $result = wp_remote_get(JAZZ_API_URL.'jobs/status/open'.$this->apikey, array('timeout' => 20));
                              if(isset($result['body']) && isset($result['headers']['content-type']) && $result['headers']['content-type']=='application/json') {
                                $rjb = array();
                                try {
                                  $resumator->jobs = json_decode($result['body'], true);
                                  foreach($resumator->jobs as $k => $v) {
                                    if(!empty($resumator_job_bank) && $v['id']==$resumator_job_bank) {
                                      $rjb['title'] = $v['title'];
                                      $rjb['id'] = $v['id'];
                                      $rjb['view'] = $respath.'job/'.$v['id'];
                                      $rjb['apply_now'] = $rjb['view'].'/apply';
                                      unset($resumator->jobs[$k]);
                                    } else {
                                      $v['view'] = $respath.'job/'.$v['id'];
                                      $v['apply_now'] = $v['view'].'/apply';
                                      $resumator->jobs[$k] = (object) $v;
                                    }
                                  }
                                  if(!empty($rjb)) {
                                    array_unshift($resumator->jobs, (object) $rjb);
                                  }
                                } catch(Exception $e) {
                                  // We should output this one. A 500.
                                  // error decoding
                                }
                                if( locate_template( array( 'jazz-jobs-page.php') ) != '' ) {
                                  require_once( locate_template( array( 'jazz-jobs-page.php' ), false, false ) );
                                } else {
                                  require_once(JAZZ_PLUGIN_DIR.DS.'template'.DS.'default.php');
                                }
                                exit();
                              }
                            }
                          break;
                        }
                      break;
                    }
                } catch (\Exception $e) {
                    // Pass through so Wordpress can (assumingly) throw it's own 404
                }
                if($response['error']!==null) {
                    // Render template here
                    exit();
                }
            }
        }

        function renderUserForm($close = false, $job_id=null) {
          $respath = get_option('resumator_url_path');

          $form_action = ($respath=='/') ? '/'.'job/'.$job_id.'/apply' : $respath . '/'.'job/'.$job_id.'/apply';

          $return = '<form enctype="multipart/form-data" method="post" id="resume-submission" action="'.$form_action.'">';

            if(isset($this->application_errors['MAINJAZZAPPERROR'])) {
              $return .= '<p class="error main-error">'.$this->application_errors['MAINJAZZAPPERROR'].'</p>';
            }

            $return .= '<div class="input';
            if(isset($this->application_errors['first_name'])) {
              $return .= ' error';
            }
            $return .= '">';
              if(isset($this->application_errors['first_name'])) {
                $return .= '<p class="error">'.$this->application_errors['first_name'].'</p>';
              }
              $return .= '<label for="resumator_first_name">First Name</label>';
              $return .= '<input type="text" id="resumator_first_name" name="first_name" ';
              if(!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) {
                $return .= 'value="'.$_POST['first_name'].'" ';
              }
              $return .= '/>';
            $return .= '</div>';

            $return .= '<div class="input';
            if(isset($this->application_errors['last_name'])) {
              $return .= ' error';
            }
            $return .= '">';
              if(isset($this->application_errors['last_name'])) {
                $return .= '<p class="error">'.$this->application_errors['last_name'].'</p>';
              }
              $return .= '<label for="resumator_last_name">Last Name</label>';
              $return .= '<input type="text" id="resumator_last_name" name="last_name" ';
              if(!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) {
                $return .= 'value="'.$_POST['last_name'].'" ';
              }
              $return .= '/>';
            $return .= '</div>';

            $return .= '<div class="input';
            if(isset($this->application_errors['email'])) {
              $return .= ' error';
            }
            $return .= '">';
              if(isset($this->application_errors['email'])) {
                $return .= '<p class="error">'.$this->application_errors['email'].'</p>';
              }
              $return .= '<label for="resumator_email">Email Address</label>';
              $return .= '<input type="text" id="resumator_email" name="email" ';
              if(!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) {
                $return .= 'value="'.$_POST['email'].'" ';
              }
              $return .= '/>';
            $return .= '</div>';

            $return .= '<div class="input';
            if(isset($this->application_errors['phone'])) {
              $return .= ' error';
            }
            $return .= '">';
              if(isset($this->application_errors['phone'])) {
                $return .= '<p class="error">'.$this->application_errors['phone'].'</p>';
              }
              $return .= '<label for="resumator_phone">Phone</label>';
              $return .= '<input type="text" id="resumator_phone" name="phone" ';
              if(!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) {
                $return .= 'value="'.$_POST['phone'].'" ';
              }
              $return .= '/>';
            $return .= '</div>';

            $return .= '<div class="input';
            if(isset($this->application_errors['coverletter'])) {
              $return .= ' error';
            }
            $return .= '">';
              if(isset($this->application_errors['coverletter'])) {
                $return .= '<p class="error">'.$this->application_errors['coverletter'].'</p>';
              }
              $return .= '<label for="resumator_coverletter">Cover Letter</label>';
              $return .= '<textarea id="resumator_coverletter" name="coverletter">';
              if((!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) && !empty($_POST['coverletter'])) {
                $return .= $_POST['coverletter'];
              }
              $return .= '</textarea>';
            $return .= '</div>';


            // $return .= '<label for="resumator_address">Address</label>';
            // $return .= '<textarea id="resumator_address" name="address"></textarea>';

            // $return .= '<div class="csz">';
            //
            //   $return .= '<label for="resumator_city">City</label>';
            //   $return .= '<input type="text" id="resumator_city" name="city" />';
            //
            //   $return .= '<label for="resumator_state">State</label>';
            //   $return .= '<input type="text" id="resumator_state" name="state" />';
            //
            //   $return .= '<label for="resumator_postal">Postal Code</label>';
            //   $return .= '<input type="text" id="resumator_postal" name="postal" />';
            //
            // $return .= '</div>';

            // $return .= '<label for="resumator_linkedin">Linkedin</label>';
            // $return .= '<input type="text" id="resumator_linkedin" name="linkedin" />';

            // $return .= '<label for="resumator_twitter">Twitter Handle</label>';
            // $return .= '<input type="text" id="resumator_twitter" name="twitter" />';

            // $return .= '<label for="resumator_website">Website</label>';
            // $return .= '<input type="text" id="resumator_website" name="website" />';

            $return .= '<fieldset class="resume';
            if(isset($this->application_errors['resume'])) {
              $return .= ' error';
            }
            $return .= '">';

            if(isset($this->application_errors['resume'])) {
              $return .= '<p class="error">'.$this->application_errors['resume'].'</p>';
            }

            $return .= '<label for="resumator_resume_file">Upload Your Resume as a PDF</label>';
            $return .= '<input type="file" id="resumator_resume_file" name="resume_file" />';

            $return .= '<p>OR</p>';

            $return .= '<label for="resumator_resumetext">Paste Your Resume Here</label>';
            $return .= '<textarea id="resumator_resumetext" name="resumetext">';
            if((!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) && !empty($_POST['resumetext'])) {
              $return .= $_POST['resumetext'];
            }
            $return .= '</textarea>';

            $return .= '</fieldset>';

            // $return .= '<label for="resumator_references">References</label>';
            // $return .= '<textarea id="resumator_references" name="references"></textarea>';

          if($close) {
            $return .= $this->close_form();
          }

          return $return;
        }

        function renderQuestionnaireForm($q_id) {
          $result = wp_remote_get(JAZZ_API_URL.'questionnaire_questions/questionnaire_id/'.$q_id.'/'.$this->apikey, array('timeout' => 20));
          if(isset($result['body'])) {
            try {
              $questions = json_decode($result['body']);
            } catch(Exception $e) {
              // error with json decode.
              // do nothing right now.
            }
          }
          
          $return = '';
          if(is_array($questions)) {

            foreach($questions as $key => $q) {

              if($q->question_status == 'Active') {

                $return .= '<fieldset class="extended_questionnaire';
                if(isset($this->application_errors['questionnaire'][$key])) {
                  $return .= ' error';
                }
                $return .= '">'."\r\n";

                if(isset($this->application_errors['questionnaire'][$key])) {
                  $return .= '<p class="error">'.$this->application_errors['questionnaire'][$key].'</p>';
                }

                if($q->question_format == 'Text Field') {

                  $return .= '<label for="'.$q->questionnaire_id.'_'.$key.'">'.$q->question_text.'</label>'."\r\n";
                  $return .= '<input type="text" id="'.$q->questionnaire_id.'_'.$key.'" name="questionnaire['.$key.']" ';
                  if(!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) {
                    $return .= 'value="'.$_POST['questionnaire'][$key].'" ';
                  }
                  $return .= '/>'."\r\n";

                } else if($q->question_format == 'Checkbox') {

                  $return .= '<fieldset class="checkbox"><legend>'.$q->question_text.'</legend>'."\r\n";

                  $answers = explode("\r\n", $q->question_answers);
                  foreach($answers as $k => $a) {

                    $return .= '<input type="hidden" value="0" name="questionnaire['.$key.']['.$a.']" />'."\r\n";
                    $return .= '<input type="checkbox" value="'.$a.'" id="'.$q->questionnaire_id.'_'.$key.'" name="questionnaire['.$key.']['.$a.']" ';
                    if((!empty($this->application_errors) || isset($this->application_errors['MAINJAZZAPPERROR'])) && isset($_POST['questionnaire'][$key][$a]) && $_POST['questionnaire'][$key][$a]!="0") {
                      $return .= 'checked ';
                    }
                    $return .= '/><label for="'.$q->questionnaire_id.'_'.$key.'">'.$a.'</label>'."\r\n";

                  }
                  
                  $return .= '</fieldset>'."\r\n";

                }

                $return .= '</fieldset>'."\r\n\r\n";

              }
            }
          }
          return $return;
        }

        private function close_form() {
          $return .= '<input type="submit" value="Submit" />';
          $return .= '</form>';

          return $return;
        }

        private function validate_application($job_id, $post) {
          $return = true;
          // set $this->application_errors;

          if(!isset($post['first_name']) || empty($post['first_name'])) {
            $return = false;
            $this->application_errors['first_name'] = 'You must provide your first name.';
          }

          if(!isset($post['last_name']) || empty($post['last_name'])) {
            $return = false;
            $this->application_errors['last_name'] = 'You must provide your last name.';
          }

          if(!isset($post['email']) || empty($post['email'])) {
            $return = false;
            $this->application_errors['email'] = 'You must provide an email address.';
          }

          if(!isset($post['phone']) || empty($post['phone'])) {
            $return = false;
            $this->application_errors['phone'] = 'You must provide a phone number.';
          }

          if(!isset($post['coverletter']) || empty($post['coverletter'])) {
            $return = false;
            $this->application_errors['coverletter'] = 'You must provide a cover letter.';
          }

          if(empty($post['resumetext']) && $_FILES['resume_file']['error']==4) {
            $return = false;
            $this->application_errors['MAINJAZZAPPERROR'] = $this->application_errors['resume'] = 'You must provide either a plaintext or PDF resume.';
          } else if(isset($_FILES['resume_file']) && $_FILES['resume_file']['error']!=4) {
            if($_FILES['resume_file']['error']==0 && $_FILES['resume_file']['type']!='application/pdf') {
              $return = false;
              $this->application_errors['MAINJAZZAPPERROR'] = $this->application_errors['resume'] = 'You must provide a PDF file as your resume.';
            } else if($_FILES['resume_file']['error']!=0) {
              $return = false;
              $this->application_errors['MAINJAZZAPPERROR'] = $this->application_errors['resume'] = 'There was an error uploading your resume.';
            }
          } else if(empty($post['resumetext'])) {
            $return = false;
            $this->application_errors['MAINJAZZAPPERROR'] = $this->application_errors['resume'] = 'You must provide either a PDF file as your resume or paste a resume into the available field.';
          }

          foreach($post['questionnaire'] as $k => $v) {
            if(is_array($v)) {
              $checked = false;
              foreach($v as $n => $i) {
                if($i==1) {
                  // checkbox, let's just make sure one of them is checked.
                  $checked = true;
                }
              }
              if(!$checked) {
                $this->application_errors['questionnaire'][$k] = 'You must check at least one option.';
              }
            } else {
              // Not checking for empty string for now.
              // if(empty($v)) {
              //   $this->application_errors['questionnaire'][$k] = 'You must provide an answer.';
              // }
            }
          }

          return $return; // or false
        }

        private function submit_application($job_id, $applicant) {
          $resumator_api_key = get_option('resumator_api_key');

          $data = array(
            'apikey' => $resumator_api_key,
            'first_name' => $applicant['first_name'],
            'last_name' => $applicant['last_name'],
            'email' => $applicant['email'],
            'phone' => $applicant['phone'],
            'coverletter' => $applicant['coverletter'],
            'job' => $job_id,
            'custom_file_privacy' => 0
          );

          // echo '<pre>';
          // var_dump($data);
          // echo '</pre>';
          // echo '<pre>';
          // var_dump($applicant);
          // echo '</pre>';
          // echo '<pre>';
          // var_dump($_FILES);
          // echo '</pre>';
          // exit();

          if(isset($applicant['resumetext']) && !empty($applicant['resumetext'])) {
            $data['resumetext'] = $applicant['resumetext'];
          } else if(isset($_FILES['resume_file']['name'])) {
            try {
              $data['base64-resume'] = base64_encode(file_get_contents($_FILES['resume_file']['tmp_name']));
            } catch(Exception $e) {
              $this->application_errors['MAINJAZZAPPERROR'] = 'We had a problem processing your resume file upload. Please try again.';
              return false;
            }
          } else {
            // Threw here if "file upload" not present on first sentence.
            $this->application_errors['MAINJAZZAPPERROR'] = 'We had a problem processing your resume. Please try again.';
            return false;
          }

          $args = array(
            'body' => json_encode($data),
            'headers' => array(
              'Data-Type' => "json",
              'Content-Type' => 'application/json'
            ),
            'timeout' => 20
          );

          // var_dump($args);
          // exit();

          $result = wp_remote_post(JAZZ_API_URL.'applicants', $args);

          // echo '<pre>';
          // var_dump($result);
          // echo '</pre>';
          // exit();

          if($result['status']=='200' || $result['response']['code']==200) {
            try {
              $rez = json_decode($result['body']);
              if(isset($rez->prospect_id)) {
                return $rez->prospect_id;
              } else {
                $this->application_errors['MAINJAZZAPPERROR'] = 'We had a problem creating your application record. Please try again.';
                return false;
              }
            } catch (Exception $e) {
              $this->application_errors['MAINJAZZAPPERROR'] = 'We had a problem creating your application record. Please try again.';
              return false;
            }
          } else {
            // ERROR!
            // Can we set a custom from the response? ....maybe.
            $this->application_errors['MAINJAZZAPPERROR'] = 'We had a problem creating your application record. Please try again.';
            return false;
          }
        }

        private function submit_questionnaire_answers($p_id, $job_id, $questionnaire_id, $answers) {
          $resumator_api_key = get_option('resumator_api_key');

          // applicant_id
          // questionnaire_id
          // job_id
          // answer_value_  01 - 20
          $data = array(
            'apikey' => $resumator_api_key,
            'applicant_id' => $p_id,
            'questionnaire_id' => $questionnaire_id,
            'job_id' => $job_id,
          );
          
          // echo '<pre>';
          // var_dump($answers);
          // echo '</pre>';
          
          foreach($answers as $key => $v) {
            if($key<20) {
              $kv = $key+1;
              if($kv<10) {
                $kv = '0'.$kv;
              }
              if(is_array($v)) {
                $f = array();
                foreach($v as $item) {
                  if($item!="0") {
                    $f[] = $item;
                  }
                }
                $data['answer_value_'.$kv] = implode(", ", $f);
              } else {
                $data['answer_value_'.$kv] = $v;
              }
            }
          }

          $args = array(
            'body' => json_encode($data),
            'headers' => array(
              'Data-Type' => "json",
              'Content-Type' => 'application/json'
            ),
            'timeout' => 20
          );
          $result = wp_remote_post(JAZZ_API_URL.'questionnaire_answers', $args);
          // echo '<pre>';
          // var_dump($result);
          // echo '</pre>';
          // exit();
          if($result['status']==200 || $result['response']['code']==200) {
            return true;
          } else {
            return false;
          }
        }
    }
}
