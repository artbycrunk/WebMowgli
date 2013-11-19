
                              <ul class="content-box-tabs nav nav-pills">

                                  {main:tab_list}

                                      <li class="{main:tab_list:selected_tab}">
                                          <a href="{main:tab_list:tab_link}" type="ajax_content" >

                                              {main:tab_list:tab_name}

                                          </a>
                                      </li> <!-- href must be unique and match the id of target div -->

                                  {/main:tab_list}

                              </ul>
