<div class="row">

    <div class="col-md-10 col-md-offset-1">
        <h1>Post via Email</h1>
        <?=$this->draw('account/menu')?>
    </div>

</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <form action="/account/emailposting/" class="form-horizontal" method="post">
            
            <div class="control-group">
                <div class="controls">
                    <h2>
                        <strong>Secret address:</strong> <?php 
                            $session = \Idno\Core\site()->session(); 
                            $user = $session->currentUser(); 
                            
                            if (!empty($user->secret_email))
                                echo "<a href=\"mailto:{$user->secret_email}\">{$user->secret_email}</a>"; 
                            else
                                echo "<em>not generated yet...</em>";
                            ?>
                    </h2>
                    <p>
                        <input type="submit" class="btn btn-large btn-success" value="Generate secret address" />
                    </p>
                </div>
            </div>
            
            
            <?= \Idno\Core\site()->actions()->signForm('/account/emailposting/')?>
        </form>
    </div>
</div>