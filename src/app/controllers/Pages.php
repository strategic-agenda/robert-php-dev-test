<?php
  class Pages extends Controller {
    public function __construct(){
     
    }
    
    public function index(){
      if(isLoggedIn()){
        redirect('posts');
      }
      $data = [
        'text1' => 'PHP Architect Test for Robert',
        'text2' => 'What is Robert?',
        'text3' => 'Robert is an innovative Computer-Assisted Translation (CAT) tool crafted to streamline translation tasks, enhancing the speed and efficiency of translators. By uploading the source document into the application, the interface intelligently segments it into translation units—be they phrases, sentences, or paragraphs—facilitating a smoother translation process.  ',
        'text4' => 'What are the units of translation?',
        'text5' => 'In the field of translation, a translation unit is a segment of a text which the translator treats as a single cognitive unit for the purposes of establishing an equivalence. It may be a single word, a phrase, one or more sentences, or even a larger unit.',
      ];
     
      $this->view('pages/index', $data);
    }
  }