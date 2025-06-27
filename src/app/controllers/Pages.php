<?php
  class Pages extends Controller {
    public function __construct(){
     
    }
    
    public function index(){
      if(isLoggedIn()){
        redirect('posts');
      }
      $data = [
        'title' => 'PHP Architect Test for Robert',
        'description' => 'What is Robert?',
        'info' => 'Robert is an innovative Computer-Assisted Translation (CAT) tool crafted to streamline translation tasks, enhancing the speed and efficiency of translators. By uploading the source document into the application, the interface intelligently segments it into translation units—be they phrases, sentences, or paragraphs—facilitating a smoother translation process.  ',
        'name' => 'What are the units of translation?',
        'location' => 'In the field of translation, a translation unit is a segment of a text which the translator treats as a single cognitive unit for the purposes of establishing an equivalence. It may be a single word, a phrase, one or more sentences, or even a larger unit.',
        'contact' => 'Instructions:',
        'mail' => '<li>Fork this repository to your own GitHub account.</li>
            <li>Create a new branch with your name for making changes.</li>
            <li>Work on the tasks listed below.</li>
            <li>Once done, create a pull request from your branch to the main repository.</li>
            <li>Record a video with voiceover, presenting the tool and its logic, and send it to us.</li>'
      ];
     
      $this->view('pages/index', $data);
    }
  }