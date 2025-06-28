<?php 

class Translations extends Controller{

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }
        //new model instance
        $this->userModel = $this->model('User');
        $this->translationModel = $this->model('Translation');
        $this->translationHistoryModel = $this->model('TranslationHistory');
    }

    public function index(){
        $translations = $this->translationModel->getTranslations();
        $data = [
            'translations' => $translations
        ];

        $this->view('translations/index', $data);
    }

    //add new translation
    public function add(){
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'source' => trim($_POST['source']),
                'translation' => trim($_POST['translation']),
                'user_id' => $_SESSION['user_id'],
                'source_err' => '',
                'translation_err' => '',
            ];

            if(empty($data['source'])){
                $data['source_err'] = 'Please enter source';
            }
            if(empty($data['translation'])){
                $data['translation_err'] = 'Please enter translation';
            }

            //validate error free
            if(empty($data['source_err']) && empty($data['translation_err'])){
                if($this->translationModel->addTranslation($data)){
                    flash('translation_message', 'Your translation have been added');
                    redirect('translations');
                }else{
                    die('something went wrong');
                }
               
                //laod view with error
            }else{
                $this->view('translations/add', $data);
            }
        }else{
            $data = [
                'source' => (isset($_POST['source']) ? trim($_POST['source']) : ''),
                'translation' =>  (isset($_POST['translation'])? trim($_POST['translation']) : '')
            ];

            $this->view('translations/add', $data);
        }
    }

    //show single translation 
    public function show($id){
        $translation = $this->translationModel->getTranslationById($id);
        $user = $this->userModel->getUserById($translation->user_id);

        $data = [
            'translation' => $translation,
            'user' => $user
        ];

        $this->view('translations/show', $data);
    }

     //edit translation
     public function edit($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'id' => $id,
                'source' => trim($_POST['source']),
                'translation' => trim($_POST['translation']),
                'user_id' => $_SESSION['user_id'],
                'source_err' => '',
                'translation_err' => '',
            ];
            //validate the source
            if(empty($data['source'])){
                $data['source_err'] = 'Please enter source';
            }
            //validate the translation
            if(empty($data['translation'])){
                $data['translation_err'] = 'Please enter translation';
            }

            //validate error free
            if(empty($data['source_err']) && empty($data['translation_err'])){
                $oldTranslation = $this->translationModel->getTranslationById($id);
                if($this->translationModel->updateTranslation($data)){
                    $data = [
                        'translation_id' => $id,
                        'old_json' => json_encode(['source' => $oldTranslation->source, 'translation' => $oldTranslation->translation]),
                        'new_json' => json_encode(['source' => $data['source'], 'translation' => $data['translation']]),
                    ];
                    $this->translationHistoryModel->addTranslationHistory($data);
                    flash('translation_message', 'Your translation have been updated');
                    redirect('translations');
                }else{
                    die('something went wrong');
                }
               
                //laod view with error
            }else{
                $this->view('translations/edit', $data);
            }
        }else{
            //check for the owner and call method from translation model
            $translation = $this->translationModel->getTranslationById($id);
            if($translation->user_id != $_SESSION['user_id']){
                redirect('translations');
            }
            $data = [
                'id' => $id,
                'source' => $translation->source,
                'translation' => $translation->translation
            ];

            $this->view('translations/edit', $data);
        }
    }
    
    //delete translation
    public function delete($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //check for owner
            $translation = $this->translationModel->getTranslationById($id);
            if($translation->user_id != $_SESSION['user_id']){
                redirect('translations');
            }
            
            //call delete method from translation model
            if($this->translationModel->deleteTranslation($id)){
                flash('translation_message', 'Translation Removed');
                redirect('translations');
            }else{
                die('something went wrong');
            }
        }else{
            redirect('translations');
        }
    }
}                            
