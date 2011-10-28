<?php 

class NodeRow
{
    var $pre = -1;
    var $post = -1;
    var $parent = -1;
    var $tag = '';
    var $val = '';  
    
    public function __construct($pre, $post, $parent, $tag, $val) 
    {
        $this->pre = $pre;
        $this->post = $post;
        $this->parent = $parent;
        $this->tag = $tag;
        $this->val = $val;
    }
    
    
    public function toPlainString($treeId = NULL)
    {
        if (is_null($treeId))
        {
            $result = "(".$treeId.", ";
        } 
        else 
        {
            $result = "(";
        }
        $result .= $this->pre.", ";
        $result .= $this->post.", ";
        $result .= $this->parent.", ";
        $result .= $this->tag.", ";
        $result .= $this->val.")";
        
        return $result;
    }
    
    public function toEncryptedString($treeId = NULL)
    {
        if (is_null($treeId))
        {
            $result = "(".$treeId.", ";
        } 
        else 
        {
            $result = "(";
        }
        $result .= $this->pre.", ";
        $result .= $this->post.", ";
        $result .= $this->parent.", ";
        
        // TODO
        // Encrypt these parameters!!!!
        $result .= $this->tag.", ";
        $result .= $this->val.")";
        
        return $result;
    }
    
    public function toEncryptedArray($treeId = NULL)
    {
        $result = array();

        if (is_null($treeId))
        {
            $result[] = $treeId;
        } 

        $result[] = $this->pre;
        $result[] = $this->post;
        $result[] = $this->parent;
        
        // TODO
        // Encrypt these parameters!!!!
        $result[] = $this->tag;
        $result[] = $this->val;
        
        return $result;
    }
    
    
}

?>