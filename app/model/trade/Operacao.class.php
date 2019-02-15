<?php
/**
 * SystemProgram
 *
 * @version    1.0
 * @package    model
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class Operacao extends TRecord
{
    const TABLENAME  = 'app_operacao';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial'; // {max, serial}
    
    // use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('data_realizacao');
        parent::addAttribute('id_parmoeda');
        parent::addAttribute('valor_entrada');
        parent::addAttribute('valor_lucro');
        parent::addAttribute('payout');
        parent::addAttribute('id_usuario');
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_groupSystem_program objects
        $id = isset($id) ? $id : $this->id;
        
        Operacao::where('id', '=', $id)->delete();
        
        // delete the object itself
        parent::delete($id);
    }
}
