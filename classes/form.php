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

        foreach ($this->inputs as $name => $input){
            switch ($input['input']){
                
                
                case 'text' :
                    $inputRules = [];
                    $inputMessages = [];
                    
                    if(isset($input['required']) && $input['required'] == true ): 
                        array_push($inputRules, 'required');
                        array_push($inputMessages, 'Merci d\'entrer une réponse');
                    endif;
                    
                    if($input['type'] == "email"): 
                        array_push($inputRules, 'email');
                        array_push($inputMessages, 'Veuillez entrer un format d\'email valide.');
                    endif;

                    $inputRules['maxLength'] = 3000;
                    array_push($inputMessages,'Votre réponse est limitée à 3000 caractères');
                    
                    $rules[$name] = [ 
                        'rules' => $inputRules,
                        'message' => $inputMessages
                    ];
                    break;

                case 'textarea' :
                    break;
               
               
                case 'checkbox' :
                    break;
                
                case 'checkbox-group' :
                    break;

        
                case 'radio-group' :
                    break;
        
    
            }
        }
        return $rules;
    }
   

}
