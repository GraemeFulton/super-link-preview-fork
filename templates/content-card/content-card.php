<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<a href="<?php echo $url; ?>">
    <div class="<?php echo $this->plugin_name; ?> link-preview">
        <?php echo $img; ?> 
        <div class="link-content">
            <div class="link-title">
                <?php echo $alt;?>
            </div>
            <div class="link-desc">
                <?php echo $desc; ?>
            </div>
            <div class="link-url">
                <?php echo $root;?>
            </div>
        </div>
            
    </div>
</a>
