<?php
/**
 * Transacao
 *
 * @version    1.0
 * @package    model
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class Transacao extends TRecord
{
    const TABLENAME  = 'app_transacao';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial'; // {max, serial}
    
    // use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('descricao');
        parent::addAttribute('valor');
        parent::addAttribute('data_transacao');
        parent::addAttribute('id_usuario');
    }

}
