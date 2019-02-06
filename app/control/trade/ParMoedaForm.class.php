<?php
/**
 * ParMoedaForm
 *
 * @version    1.0
 * @package    control
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class ParMoedaForm extends TStandardForm
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('database');              // defines the database
        $this->setActiveRecord('ParMoeda');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ParMoeda');
        $this->form->setFormTitle('Par Moeda');
        
        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel('Descrição')], [$descricao] );
        $id->setEditable(FALSE);
        $id->setSize('30%');
        $descricao->setSize('70%');
        $descricao->addValidation('Descrição', new TRequiredValidator );
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('Clear'),  new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addAction(_t('Back'),new TAction(array('ParMoedaList','onReload')),'fa:arrow-circle-o-left blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'ParMoedaList'));
        $container->add($this->form);
        
        parent::add($container);
    }
}
