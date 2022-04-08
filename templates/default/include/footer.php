    

  </main>


<!--================ Start footer Area  =================-->	
  <footer class="footer">
      <div class="footer-area">
          <div class="container">
              <div class="row section_gap">
                  <div class="col-lg-3 col-md-6 col-sm-6">
                      <div class="single-footer-widget tp_widgets">
                          <h4 class="footer_title large_title">Our Mission</h4>
                          <?=$this->set['description']?>
                      </div>
                  </div>
                  <div class="offset-lg-1 col-lg-2 col-md-6 col-sm-6">
                      <div class="single-footer-widget tp_widgets">
                          <h4 class="footer_title">Quick Links</h4>
                          <ul class="list">
                              <?php $keywords = explode(',', $this->set['keywords']);
                                    foreach ($keywords as $key){
                                        echo '<li><a href="'.$this->link([$key]).'">'. $key .'</a></li>';                                       
                                    }

                              ?>
                          </ul>
                      </div>
                  </div>
                  <div class="col-lg-2 col-md-6 col-sm-6">
                      <div class="single-footer-widget instafeed">
                          <h4 class="footer_title">Gallery</h4>
                          <ul class="list instafeed d-flex flex-wrap">

                            <?php if ($this->set['gallery']) :?>

                                <?php $this->set['gallery'] = json_decode($this->set['gallery']);?>

                                <?php foreach ($this->set['gallery'] as $file) :?>
                                        <li><img src="<?=PATH . UPLOAD_DIR . $file?>" class="gn-img-size" alt="..."></li>                                      
                                <?php endforeach; ?>
                                    
                            <?php endif; ?>

                          </ul>
                      </div>
                  </div>
                  <div class="offset-lg-1 col-lg-3 col-md-6 col-sm-6">
                      <div class="single-footer-widget tp_widgets">
                          <h4 class="footer_title">Contact Us</h4>
                          <div class="ml-40">
                              <p class="sm-head">
                                  <span class="fa fa-location-arrow"></span>
                                  Head Office
                              </p>
                              <p><?=$this->set['address']?></p>
  
                              <p class="sm-head">
                                  <span class="fa fa-phone"></span>
                                  Phone Number
                              </p>
                              <p>
                                <?php $phones = explode('<br>', $this->set['phone']);
                                        foreach ($phones as $phone){
                                            echo '<a href="tel:'. preg_replace('/[^\+\d]/', '', $phone) .'">'. $phone .'</a>';                                       
                                        }

                                ?>
                              </p>
  
                              <p class="sm-head">
                                  <span class="fa fa-envelope"></span>
                                  Email
                              </p>
                              <p>
                                <?=$this->set['email']?> 
                              </p>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <div class="footer-bottom">
          <div class="container">
              <div class="row d-flex">
                  <p class="col-lg-12 footer-text text-center">
                      <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>
<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>
              </div>
          </div>
      </div>
  </footer>
  <!--================ End footer Area  =================-->



    <?=$this->getScripts();?>
</body>
</html>