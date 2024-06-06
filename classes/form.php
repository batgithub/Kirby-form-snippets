<?php 
namespace repliq;
class RepliqForm {
    public $inputs;

    public function __construct (array $inputs)
    {
       $this->inputs = $inputs;
    }


    public function getRules(): array
    {
        $rules = [];

        foreach ($this->inputs as $id => $input){
            switch ($input['input']){
                case 'honeypot' :
                    $inputRules = [];
                    $inputMessages = [];
                    
                    $inputRules['maxLength'] = 3000;
                    array_push($inputMessages,"Ouups, quelque chose s'est mal passé. Si le problème persiste contactez moi par mail directement");
                   
                    $rules[$id] = [ 
                        'rules' => $inputRules,
                        'message' => $inputMessages
                    ];

                    break;

                case 'input' :
                    $inputRules = [];
                    $inputMessages = [];
                    
                    if(isset($input['required']) && $input['required'] == true ): 
                        array_push($inputRules, 'required');
                        array_push($inputMessages, isset($input['error']) ? $input['error'] : 'Merci d\'entrer une réponse');
                    endif;
                    
                    if($input['type'] == "email"): 
                        array_push($inputRules, 'email');
                        array_push($inputMessages, isset($input['error']) ? $input['error'] : 'Veuillez entrer un format d\'email valide.');
                    endif;

                    if($input['type'] == "phone"): 
                        array_push($inputRules, 'tel');
                        array_push($inputMessages, isset($input['error']) ? $input['error'] : 'Veuillez entrer un format de téléphone valide.');
                    endif;

                    $inputRules['maxLength'] = isset($input['maxlength']) ? $input['maxlength'] : '1000';
                    $inputRules['minLength'] =  isset($input['minlength']) ? $input['minlength'] : '10';
                    array_push($inputMessages,'Votre réponse est limitée à 1000 caractères');
                    
                    $rules[$id] = [ 
                        'rules' => $inputRules,
                        'message' => $inputMessages
                    ];
                    break;

                case 'textarea' :
                    $inputRules = [];
                    $inputMessages = [];
                    
                    if(isset($input['required']) && $input['required'] == true ): 
                        array_push($inputRules, 'required');
                        array_push($inputMessages, isset($input['error']) ? $input['error'] : 'Merci d\'entrer une réponse');
                    endif;
                    $inputRules['maxLength'] = isset($input['maxlength']) ? $input['maxlength'] : '3000';
                    $inputRules['minLength'] =  isset($input['minlength']) ? $input['minlength'] : '10';
                    array_push($inputMessages,'Votre réponse est limitée à 3000 caractères');
                    
                    $rules[$id] = [ 
                        'rules' => $inputRules,
                        'message' => $inputMessages
                    ];
                    break;

                case 'select' :
                    $inputRules = [];
                    $inputMessages = [];
                    
                    if(isset($input['required']) && $input['required'] == true ): 
                        array_push($inputRules, 'required');
                        array_push($inputMessages, isset($input['error']) ? $input['error'] : 'Merci de selectionner un élément');
                    endif;

                    $rules[$id] = [ 
                        'rules' => $inputRules,
                        'message' => $inputMessages
                    ];

                    break;
               
                case 'checkbox' :
                    $rules[$id] = [];

                    break;
                
                case 'checkbox-group' :
                    $rules[$id] = [];
                    break;

        
                case 'radio-group' :
                    $rules[$id] = [];
                    break;
        
    
            }
        }
        return $rules;
    }

    public function getInputs(): array
    {
        $snippets = [];

        foreach ($this->inputs as $id => $input){
            $params = [];  
            foreach( $input as $key => $param):
                if($key !== "input"){
                    $params[$key] =  $param;
                }
            endforeach;

            $snippets[$id] = snippet('form-'.$input['input'] , $params);

        }
        return $snippets;

    }    
   

}
