<form class="container" action="<?= BASE_URL . "comment/addGuest" ?>" method="post">
          <div class="datacomment">
            <p class="textcommentauthor">Add new comment:</p>
            <textarea name="comment" id="comment"></textarea>
            <p class="important"><?= $errors['comment']?></p>
            <input type="hidden" name="id" value="<?= $journey['id']?>" />
            <br>
            <input type="text" name="username" id="username" placeholder="Guest username" />
            <p class="important"><?= $errors['username']?></p>
            <div id="commentbtn"><input type="submit" value="POST"></div>
          </div>
        </form>