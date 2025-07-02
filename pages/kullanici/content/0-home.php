                            <input type="hidden" class="form-control" id="user_id" name="user_id"
                                value="<?php echo $_GET["id"] ?? 0 ?>">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label for="full_name" class="fw-semibold">Adı Soyadı: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-user-plus"></i></div>
                                                <input type="text" class="form-control" name="adi_soyadi"
                                                    value="<?php echo $user->full_name ?? '' ?>" id="full_name"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label for="password" class="fw-semibold">Parola: </label>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-key"></i></div>
                                                <input type="password" autocomplete="off" class="form-control"
                                                    name="password" value="" id="password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label for="eposta" class="fw-semibold">Eposta Adresi: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-mail"></i></div>
                                                <input type="text" class="form-control" name="email_adresi"
                                                    value="<?php echo $user->email ?? '' ?>" id="email" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label for="password" class="fw-semibold">Telefon: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-phone"></i></div>
                                                <input type="text" class="form-control" name="phone"
                                                    value="<?php echo $user->phone ?? '' ?>" id="phone">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label for="userroles" class="fw-semibold">Kullanıcı Rolü: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-user-plus"></i></div>
                                                <?php echo $UserHelper->userRoles("user_roles", $user->roles ?? '') ?>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label for="job" class="fw-semibold">Mesleği: </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-pen-tool"></i></div>
                                                <input type="text" class="form-control" name="job"
                                                    value="<?php echo $user->job ?? '' ?>" id="job">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>