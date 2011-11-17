<?php 

class NodeRow
{
    var $treeId = '';
    var $pre = -1;
    var $post = -1;
    var $parent = -1;
    var $tag = '';
    var $val = '';  
    var $encryption_key = '';
    var $hash_key = '';
    var $enctag = '';
    var $encval = '';
    
    /**
     * Keys should be base64_decode()-ed
     */
    public function __construct($encryption_key, $hash_key, $treeId, $pre, $post, $parent, $tag, $val, $tagAndValEncrypted = false) 
    {
        $this->encryption_key = $encryption_key;
        $this->hash_key = $hash_key;
        $this->treeId = $treeId;
        $this->pre = $pre;
        $this->post = $post;
        $this->parent = $parent;
        $this->tag = $tag;
        $this->val = $val;
        
        if ($tagAndValEncrypted)
        {
            $this->encval = $val;
            $this->enctag = $tag;
            
            // We want to save 'tag' and 'val' in decrypted format, so decrypt them.
            $this->val = SdmSymmetricCrypt::decryptSearchResults($this->encryption_key, $this->hash_key, $this->val, $this->pre);
            $this->tag = SdmSymmetricCrypt::decryptSearchResults($this->encryption_key, $this->hash_key, $this->tag, $this->pre);
        }
    }
    
    public function isTextNode() 
    {
        return (isset($this->tag) && ($this->tag == "#TEXT"));
    }
    
    public function isAttrNode()
    {
        return (isset($this->tag) && (substr($this->tag, 0, 1) == "@"));
    }
    
    
    public function toPlainString()
    {
        $result = "(".$this->treeId.", ";
        $result .= $this->pre.", ";
        $result .= $this->post.", ";
        $result .= $this->parent.", ";
        $result .= $this->tag.", ";
        $result .= $this->val.")";
        
        return $result;
    }
    
    public function toEncryptedString()
    {
        $result = "(".$this->treeId.", ";
        $result .= $this->pre.", ";
        $result .= $this->post.", ";
        $result .= $this->parent.", ";
        
        $result .= SdmSymmetricCrypt::encryptForSearching($this->encryption_key, $this->hash_key, $this->tag, $this->pre);
        $result .= SdmSymmetricCrypt::encryptForSearching($this->encryption_key, $this->hash_key, $this->val, $this->pre);
        
        return $result;
    }
    
    public function toArray()
    {
        $result = array();
        
        $result[] = $this->treeId;
        $result[] = $this->pre;
        $result[] = $this->post;
        $result[] = $this->parent;
        $result[] = $this->tag;
        $result[] = $this->val;
        
        return $result;
    }
    
    
    public function toEncryptedArray()
    {
        $result = array();

        $result[] = $this->treeId;
        $result[] = $this->pre;
        $result[] = $this->post;
        $result[] = $this->parent;
        
        $result[] = SdmSymmetricCrypt::encryptForSearching($this->encryption_key, $this->hash_key, $this->tag, $this->pre);
        $result[] = SdmSymmetricCrypt::encryptForSearching($this->encryption_key, $this->hash_key, $this->val, $this->pre);
        
        echo "\nRow::(".$result[0].", ".$result[1].", ".$result[2].", ".$result[3].", ".$result[4].", ".$result[5].",)";
        
        return $result;
    }
    
    
}

?>