<?= $this->extend('dashboard/layouts/main'); ?>

<?= $this->section('styles') ?>
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/filepond-plugin-media-preview@1.0.11/dist/filepond-plugin-media-preview.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/pages/form-element-select.css'); ?>">
<style>
    .filepond--root {
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<section class="section text-dark">
    <div class="row">
        <script>
            currentUrl = '<?= current_url(); ?>';
        </script>

        <!-- Object Detail Information -->
        <div class="col-md-6 col-12">
            <div class="card">
                <div class="card-header text-center">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Homestay Reservation</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col table-responsive">
                            <table class="table table-borderless text-dark">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">ID</td>
                                        <td><?= esc($reservation['id']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Request Date</td>
                                        <td><?= esc(date_format(date_create($reservation['request_date']), "d F Y, H:i")); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Check In</td>
                                        <td><?= esc(date_format(date_create($reservation['check_in']), "d F Y, H:i")); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Check Out</td>
                                        <td><?= esc(date_format(date_create($reservation['check_out']), "d F Y, H:i")); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Day of Stay</td>
                                        <td><?= esc($reservation['day_of_stay']); ?> Days</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Total People</td>
                                        <td><?= esc($reservation['total_people']); ?> people</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Contact Person</td>
                                        <td><?= esc($homestay['phone']); ?></td>
                                    </tr>
                                    <?php if ($reservation['rating'] != null) : ?>
                                        <tr>
                                            <td class="fw-bold">Rating</td>
                                            <td>
                                                <?php for ($i = 0; $i < (int)esc($reservation['rating']); $i++) { ?>
                                                    <i class="fa-solid fa-star fs-4 star-checked"></i>
                                                <?php } ?>
                                                <?php for ($i = 0; $i < (5 - (int)esc($reservation['rating'])); $i++) { ?>
                                                    <i class="fa-solid fa-star fs-4"></i>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Review</td>
                                            <td><?= esc($reservation['review']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="accordion" id="accordionPanelsStayOpenExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                        Homestay Units
                                    </button>
                                </h2>
                                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                                    <div class="accordion-body">
                                        <span class="fw-bold"><?= esc($homestay['name']) ?></span><br>
                                        <span>Type : <?= esc($homestay['unit_type']) ?></span>
                                        <?php $homestay_unit_total_price = 0; ?>
                                        <?php foreach ($homestay_unit as $item) : ?>
                                            <div class="card border mb-3">
                                                <div class="row g-0">
                                                    <div class="col-md-3 d-flex align-items-center justify-content-center">
                                                        <img width="500px" src="/media/photos/<?= esc($item['galleries'][0]) ?>" class="img-fluid rounded-start" alt="..." style="object-fit: cover; height: 150px;">
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <h5 class="card-title"><?= esc($item['name']) ?><a class="text-info float-end" target="_blank" href="/web/homestayUnit/<?= esc($item['homestay_id']) ?>/detail/<?= esc($item['unit_type']) ?><?= esc($item['unit_number']) ?>"><i class="fa-solid fa-circle-info"></i></a></h5>
                                                            </div>
                                                            <p class="card-text text-truncate"><?= esc($item['description']) ?></p>
                                                            <p class="card-text"><small class="text-dark"><?= esc("Rp " . number_format($item['price'], 0, ',', '.')); ?>/day</small></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $homestay_unit_total_price = $homestay_unit_total_price + ($item['price'] * $reservation['day_of_stay']); ?>
                                        <?php endforeach; ?>
                                        <span>Homestay unit total price = <?= esc("Rp " . number_format($homestay_unit_total_price, 0, ',', '.')) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="panelsStayOpen-headingFour">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="true" aria-controls="panelsStayOpen-collapseFour">
                                        Additional Amenities
                                    </button>
                                </h2>
                                <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingFour">
                                    <div class="accordion-body">
                                        <div class="mb-3">
                                            <span class="fw-bold"><?= esc($homestay['name']) ?></span>
                                        </div>
                                        <?php $homestay_activity_total_price = 0; ?>
                                        <?php if (!empty($reservation_additional_amenities)) : ?>
                                            <?php foreach ($reservation_additional_amenities as $activity) : ?>

                                                <li><?= esc($activity['name']) ?>
                                                    <a class="text-info float-end" target="_blank"><i class="fa-solid fa-circle-info" data-bs-toggle="modal" data-bs-target="#infoActivity<?= esc($activity['id']); ?>"></i></a><br>
                                                    <p class="ms-4">
                                                        <?= esc("Rp " . number_format($activity['price'], 0, ',', '.')); ?><?= ($activity['is_order_count_per_day'] == '1') ? '/day' : '' ?><?= ($activity['is_order_count_per_person'] == '1') ? '/person' : '' ?><?= ($activity['is_order_count_per_room'] == '1') ? '/room' : '' ?>
                                                        <br>
                                                        <?= ($activity['day_order'] != '0') ? 'Day Order : ' . $activity['day_order'] . ', ' : '' ?><?= ($activity['person_order'] != '0') ? 'Person Order : ' . $activity['person_order'] . ', ' : '' ?><?= ($activity['room_order'] != '0') ? 'Room Order : ' . $activity['room_order'] . ', ' : '' ?><?= (($activity['day_order'] == '0') && ($activity['person_order'] == '0') && ($activity['room_order'] == '0')) ? 'Total Order : ' . $activity['total_order']  : '' ?>
                                                        <br>
                                                        <?= esc("Price : Rp " . number_format($activity['total_price'], 0, ',', '.')); ?>
                                                    </p>
                                                </li>


                                            <?php
                                                $homestay_activity_total_price = $homestay_activity_total_price + $activity['total_price'];
                                            endforeach;
                                            ?>
                                            <span>Homestay additional amenities total price = <?= esc("Rp " . number_format($homestay_activity_total_price, 0, ',', '.')) ?></span>
                                        <?php else : ?>
                                            <span>No additional amenities added</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-12">
            <div class="card">
                <div class="card-header text-center">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Package Reservation</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php $package_total_price = 0; ?>
                    <?php if (empty($package)) : ?>
                        <center>
                            <span>No tourism packages purchased</span>
                        </center>
                    <?php else : ?>
                        <div class="card border mb-3">
                            <div class="row">
                                <?php if ($package['brochure_url'] != null) : ?>
                                    <div class="col-md-3 d-flex align-items-center justify-content-center">
                                        <img width="500px" src="/media/photos/<?= esc($package['brochure_url']) ?>" class="img-fluid rounded-start" alt="..." style="object-fit: cover; height: 170px;">
                                    </div>
                                <?php endif; ?>
                                <?php if ($package['brochure_url'] != null) : ?>
                                    <div class="col-md-9">
                                    <?php else : ?>
                                        <div class="col-md-12">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col">
                                                    <h5 class="card-title"><?= esc($package['name']) ?></h5>
                                                </div>
                                                <div class="col">
                                                    <a title="Detail Package" class="btn icon btn-outline-info btn-sm mb-1 me-1 float-end" href="/web/homestayPackage/detail/<?= esc($package['id']); ?>" target="_blank">
                                                        <i class="fa-solid fa-circle-info"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <p class="card-text">Minimun Capacity : <?= esc($package['min_capacity']) ?> people</p>
                                            <p class="card-text"><small class="text-dark"><?= esc("Rp " . number_format($package['price'], 0, ',', '.')); ?></small></p>
                                        </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="accordion mt-3" id="accordionPanelsStayOpenExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="true" aria-controls="panelsStayOpen-collapseTwo">
                                            Package Activity
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingTwo">
                                        <div class="accordion-body">
                                            <?php foreach ($list_day as $day) : ?>
                                                <div class="mt-3">
                                                    <span class="fw-bold">Day <?= esc($day['day']); ?></span>
                                                    <?php foreach ($list_activity as $activity) : ?>
                                                        <?php if ($activity['day'] == $day['day']) : ?>
                                                            <li><?= esc($activity['object_name']); ?>
                                                                <?php if ($activity['description'] != null) : ?>
                                                                    <?= esc(' : ' . $activity['description']); ?>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="true" aria-controls="panelsStayOpen-collapseThree">
                                            Package Service
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingThree">
                                        <div class="accordion-body">
                                            <div class="mt-3">
                                                <span class="fw-bold">Include</span>
                                                <?php foreach ($list_service as $service) : ?>
                                                    <?php if ($service['status'] == '1') : ?>
                                                        <li><?= esc($service['name']); ?> </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                <div class="mt-3">
                                                    <span class="fw-bold">Exclude</span>
                                                    <?php foreach ($list_service as $service) : ?>
                                                        <?php if ($service['status'] == '0') : ?>
                                                            <li><?= esc($service['name']); ?> </li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col table-responsive mt-3">
                                <table class="table table-borderless text-dark">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Total People</td>
                                            <td><?= esc($reservation['total_people']); ?> People</td>
                                        </tr>
                                        <?php

                                        if ($package['min_capacity'] == 0) {
                                            $packageOrder = 1;
                                        } else {
                                            $packageOrder = $reservation['total_people'] / $package['min_capacity'];
                                            if ($packageOrder < 1) {
                                                $packageOrder = 1;
                                            } elseif (($reservation['total_people'] % $package['min_capacity'] <= $package['min_capacity'] / 2) && ($reservation['total_people'] % $package['min_capacity'] > 0)) {
                                                $packageOrder = floor($packageOrder) + 0.5;
                                            } elseif ($reservation['total_people'] % $package['min_capacity'] > $package['min_capacity'] / 2) {
                                                $packageOrder = floor($packageOrder) + 1;
                                            }
                                        }
                                        $package_total_price = $packageOrder * $package['price'];
                                        ?>
                                        <tr>
                                            <td class="fw-bold">Package Order</td>
                                            <td><?= esc($packageOrder); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Package Reservation<br>Total Price</td>
                                            <td><?= esc("Rp " . number_format($package_total_price, 0, ',', '.')); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        </div>
                </div>
                <?php
                $total_price = $homestay_unit_total_price + $homestay_activity_total_price + $package_total_price;
                $deposit = $total_price * 20 / 100;
                $fullPay = $total_price * 80 / 100;

                if ($reservation['is_refund'] == '1') {
                    $refund = $deposit * 50 / 100;
                } elseif ($reservation['is_refund'] == '0') {
                    $refund = 0;
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="card">
                <div class="card-header text-center">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Transaction History</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="col table-responsive">
                        <table class="table table-borderless text-dark">
                            <tbody>
                                <tr>
                                    <td class="fw-bold">Total Price</td>
                                    <td>: <?= esc("Rp " . number_format($total_price, 0, ',', '.')) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Deposit</td>
                                    <td>
                                        : <?= esc("Rp " . number_format($deposit, 0, ',', '.')) ?> <i>*(20% of total price)</i>
                                        <?php if (($reservation['status'] == '1') && ($reservation['deposit_proof'] == null) && ($reservation['is_rejected'] != '1')) : ?>
                                            <?php
                                            $depositDeadline = date("d F Y, H:i", strtotime($reservation['check_in'] . ' - 2 days'));;
                                            ?>
                                            <span class="text-danger">(Deadline : <?= esc($depositDeadline) ?>)</span>
                                        <?php endif; ?>
                                        <?php if ($reservation['deposit_confirmed_at'] != null) : ?>
                                            <span class="text-success">(Paid by customer)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (($reservation['deposit_confirmed_at'] != null) && ($reservation['cancelation_reason'] != '1')) : ?>
                                    <tr>
                                        <td class="fw-bold">Full Pay</td>
                                        <td>
                                            : <?= esc("Rp " . number_format($fullPay, 0, ',', '.')) ?> <i>*(80% of total price)</i>
                                            <?php if (($reservation['full_paid_proof'] == null) && ($reservation['full_paid_confirmed_at'] == null)) : ?>
                                                <?php
                                                $fullPayDeadline = date("d F Y 18:00", strtotime($reservation['check_in']));
                                                ?>
                                                <span class="text-danger">(Deadline : <?= esc($fullPayDeadline) ?>)</span>
                                            <?php endif; ?>
                                            <?php if ($reservation['full_paid_confirmed_at'] != null) : ?>
                                                <span class="text-success">(Paid by customer)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['is_refund'] == '1') : ?>
                                    <tr>
                                        <td class="fw-bold">Refund</td>
                                        <td>
                                            : <?= esc("Rp " . number_format($refund, 0, ',', '.')) ?> <i>*(50% of deposit)</i>
                                            <?php if ($reservation['refund_paid_confirmed_at'] != null) : ?>
                                                <span class="text-success">(Paid by homestay owner)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (($reservation['is_refund'] == '0') && ($reservation['cancelation_reason'] == '1')) : ?>
                                    <tr>
                                        <td class="fw-bold">Refund</td>
                                        <td>: <?= esc("Rp 0") ?> <i>*(canceled after 1 day before check in)</i></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <hr class="hr">
                        <table class="table table-borderless text-dark">
                            <tbody>
                                <?php if ($reservation['reservation_finish_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Reservation Finish at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['reservation_finish_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['confirmed_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Reservation Confirmed at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['confirmed_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['deposit_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Deposit Proof</td>
                                        <td>: <a href="/media/photos/<?= esc($reservation['deposit_proof']) ?>" target="_blank">See Document</a></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Deposit Proof uploaded at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['deposit_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['deposit_confirmed_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Deposit Confirmed at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['deposit_confirmed_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['canceled_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Canceled at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['canceled_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Cancelation Reason</td>
                                        <?php if ($reservation['cancelation_reason'] == '1') : ?>
                                            <td>: Canceled by customer</td>
                                        <?php elseif ($reservation['cancelation_reason'] == '2') : ?>
                                            <td>: Deposit payment has exceeded the deadline</td>
                                        <?php elseif ($reservation['cancelation_reason'] == '3') : ?>
                                            <td>: Full payment has exceeded the deadline</td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['refund_paid_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Refund Proof</td>
                                        <td>: <a href="/media/photos/<?= esc($reservation['refund_proof']) ?>" target="_blank">See Document</a></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Refund Proof uploaded at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['refund_paid_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['refund_paid_confirmed_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Refund Paid Confirmed at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['refund_paid_confirmed_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['full_paid_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Full Paid Proof</td>
                                        <td>: <a href="/media/photos/<?= esc($reservation['full_paid_proof']) ?>" target="_blank">See Document</a></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Full Paid Proof uploaded at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['full_paid_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($reservation['full_paid_confirmed_at'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Full Paid Confirmed at</td>
                                        <td>: <?= esc(date_format(date_create($reservation['full_paid_confirmed_at']), "d F Y, H:i")) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="fw-bold">Status</td>
                                    <td>:
                                        <?php if (($reservation['status'] == null) && ($reservation['canceled_at'] == null)) : ?>
                                            <button title="Reservation Incomplete" class="btn-sm btn-dark float-center" disabled>Incomplete</button>
                                        <?php elseif (($reservation['status'] == '0') && ($reservation['canceled_at'] == null)) : ?>
                                            <button title="Waiting for the homestay owner to accept the reservation" class="btn-sm btn-warning float-center" disabled>Waiting</button>
                                        <?php elseif (($reservation['status'] == '1') && ($reservation['deposit_proof'] == null) && ($reservation['canceled_at'] == null) && ($reservation['is_rejected'] == '1')) : ?>
                                            <button title="Reservation Rejected" class="btn-sm btn-danger float-center" disabled>Rejected</button>
                                        <?php elseif (($reservation['status'] == '1') && ($reservation['deposit_proof'] == null) && ($reservation['canceled_at'] == null)) : ?>
                                            <button title="Paying Deposit" class="btn-sm btn-success float-center" disabled>Paying Deposit</button>
                                        <?php elseif (($reservation['deposit_proof'] != null) && ($reservation['deposit_confirmed_at'] == null)) : ?>
                                            <button title="Waiting for the homestay owner to confirm deposit payment" class="btn-sm btn-warning float-center" disabled>Waiting</button>
                                        <?php elseif (($reservation['canceled_at'] != null) && ($reservation['is_refund'] == '0')) : ?>
                                            <button title="Reservation Canceled" class="btn-sm btn-danger float-center" disabled>Cancel</button>
                                        <?php elseif (($reservation['canceled_at'] != null) && ($reservation['is_refund'] == '1') && ($reservation['refund_proof'] == null)) : ?>
                                            <button title="Waiting for the homestay owner to pay refund" class="btn-sm btn-danger float-center" disabled>Refund</button>
                                        <?php elseif (($reservation['canceled_at'] != null) && ($reservation['is_refund'] == '1') && ($reservation['refund_proof'] != null) && ($reservation['refund_paid_confirmed_at'] == null)) : ?>
                                            <button title="Waiting for the customer to confirm refund" class="btn-sm btn-danger float-center" disabled>Confirm Refund</button>
                                        <?php elseif (($reservation['canceled_at'] != null) && ($reservation['is_refund'] == '1') && ($reservation['refund_proof'] != null) && ($reservation['refund_paid_confirmed_at'] != null)) : ?>
                                            <button title="Reservation Canceled" class="btn-sm btn-danger float-center" disabled>Cancel</button>
                                        <?php elseif (($reservation['deposit_confirmed_at'] != null) && ($reservation['full_paid_proof'] == null)) : ?>
                                            <button title="Paying Full Price" class="btn-sm btn-success float-center" disabled>Paying Full Price</button>
                                        <?php elseif (($reservation['full_paid_proof'] != null) && ($reservation['full_paid_confirmed_at'] == null)) : ?>
                                            <button title="Waiting for the homestay owner to confirm full payment" class="btn-sm btn-warning float-center" disabled>Waiting</button>
                                        <?php elseif ($reservation['full_paid_confirmed_at'] != null) : ?>
                                            <button title="Reservation Done" class="btn-sm btn-primary float-center" disabled>Done</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($reservation['feedback'] != null) : ?>
                                    <tr>
                                        <td class="fw-bold">Feedback</td>
                                        <td>: <?= esc($reservation['feedback']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="2">
                                        <hr class="hr">
                                    </td>
                                </tr>
                                <?php if (($reservation['status'] == '0') && ($reservation['confirmed_at'] == null) && ($reservation['canceled_at'] == null)) : ?>
                                    <tr>
                                        <td colspan="2">
                                            <span class="fw-bold">To Do : </span>
                                            Confirm reservation.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <a title="Confirm Reservation" class="btn icon btn-primary btn-sm mb-1 mt-3" data-bs-toggle="modal" data-bs-target="#confirmReservation">
                                                Confirm Reservation
                                            </a>
                                        </td>
                                    </tr>
                                <?php elseif (($reservation['deposit_at'] != null) && ($reservation['deposit_confirmed_at'] == null)) : ?>
                                    <tr>
                                        <td colspan="2">
                                            <span class="fw-bold">To Do : </span>
                                            Confirm deposit payment. Please check the deposit proof first before confirm.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <a title="Confirm Deposit" class="btn icon btn-primary btn-sm mb-1 mt-3" data-bs-toggle="modal" data-bs-target="#confirmDeposit">
                                                Confirm Deposit
                                            </a>
                                        </td>
                                    </tr>
                                <?php elseif (($reservation['canceled_at'] != null) && ($reservation['is_refund'] == '1') && ($reservation['refund_paid_confirmed_at'] == null)) : ?>
                                    <tr>
                                        <td colspan="2">
                                            <span class="fw-bold">To Do : </span>
                                            Pay refund and then upload payment proof.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            Pay refund to customer account :
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <?php if ($customer_bank_account == null) : ?>
                                                <span><i>Customer has not entered his bank account data</i></span>
                                            <?php else : ?>
                                                <div class="col-md-4">
                                                    <div class="card border" style="display: flex;">
                                                        <div class="card-body">
                                                            <span class="fw-bold">
                                                                <?= esc($customer_bank_account['bank_name']) ?><?= ($customer_bank_account['bank_code']) ? ' (' . $customer_bank_account['bank_code'] . ')' : '' ?>
                                                            </span>
                                                            <br>
                                                            Account Number : <?= esc($customer_bank_account['account_number']) ?>
                                                            <br>
                                                            Account Name : <?= esc($customer_bank_account['account_name']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mt-5">
                                                    <form class="form form-vertical" action="/dashboard/reservation/refund/<?= esc($reservation['id']); ?>" method="post" enctype="multipart/form-data">
                                                        <div class="form-body">
                                                            <div class="form-group mb-4">
                                                                <label for="gallery" class="form-label">
                                                                    <?php if ($reservation['refund_proof'] == null) : ?>
                                                                        Refund Proof
                                                                    <?php else : ?>
                                                                        Change Refund Proof
                                                                        <?php if ($reservation['is_refund_proof_correct'] == '0') : ?>
                                                                            <br>
                                                                            <span class="text-danger"><i>*refund proof that you uploaded previously is incorrect</i></span>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                </label>
                                                                <input class="form-control" accept="image/*" type="file" name="gallery[]" id="gallery" required>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Save</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php elseif (($reservation['full_paid_at'] != null) && ($reservation['full_paid_confirmed_at'] == null)) : ?>
                                    <tr>
                                        <td colspan="2">
                                            <span class="fw-bold">To Do : </span>
                                            Confirm full payment. Please check the full payment proof first before confirm.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <a title="Confirm Full Payment" class="btn icon btn-primary btn-sm mb-1 mt-3" data-bs-toggle="modal" data-bs-target="#confirmFullPay">
                                                Confirm Full Payment
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Info Activity -->
    <?php if (!empty($reservation_additional_amenities)) : ?>
        <?php foreach ($reservation_additional_amenities as $activity) : ?>
            <div class="modal fade bd-example-modal-lg" id="infoActivity<?= esc($activity['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Info Activity</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="card mb-3" style="max-width: 1000px;">
                                <div class="row g-0">
                                    <div class="col-md-6 d-flex align-items-center justify-content-center">
                                        <img width="1000px" src="<?= base_url('media/photos'); ?>/<?= esc($activity['image_url']); ?>" class="img-fluid rounded-start" alt="..." style="object-fit: cover;" height="250px">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= esc($activity['name']); ?>
                                                <?php if (!empty($activity['category'] == '1')) : ?>
                                                    (Facility)
                                                <?php else : ?>
                                                    (Service)
                                                <?php endif; ?>
                                            </h5>
                                            <p class="card-text"><?= esc($activity['description']); ?></p>
                                            <p class="card-text"><small class="text-dark"><?= esc("Rp " . number_format($activity['price'], 0, ',', '.')) ?><?= ($activity['is_order_count_per_day'] == '1') ? '/day' : '' ?><?= ($activity['is_order_count_per_person'] == '1') ? '/person' : '' ?><?= ($activity['is_order_count_per_room'] == '1') ? '/room' : '' ?></small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <!-- Modal Info Activity -->
    <?php if (!empty($reservation_homestay_activity)) : ?>
        <?php foreach ($reservation_homestay_activity as $activity) : ?>
            <div class="modal fade bd-example-modal-lg" id="infoActivity<?= esc($activity['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Info Activity</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="card mb-3" style="max-width: 1000px;">
                                <div class="row g-0">
                                    <div class="col-md-6 d-flex align-items-center justify-content-center">
                                        <img width="1000px" src="<?= base_url('media/photos'); ?>/<?= esc($activity['image_url']); ?>" class="img-fluid rounded-start" alt="..." style="object-fit: cover;" height="250px">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= esc($activity['name']); ?></h5>
                                            <p class="card-text"><?= esc($activity['description']); ?></p>
                                            <p class="card-text"><small class="text-dark"><?= esc("Rp " . number_format($activity['price'], 0, ',', '.')) ?>/person</small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Modal Confirm Reservation -->
    <?php if (($reservation['status'] == '0') && ($reservation['confirmed_at'] == null) && ($reservation['canceled_at'] == null)) : ?>
        <div class="modal fade" id="confirmReservation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Reservation Confirmation</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card-body text-dark">
                            <form class="form form-vertical" action="/dashboard/reservation/confirm/<?= esc($reservation['id']); ?>" method="post" onsubmit="checkRequired(event)" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="form-group mb-4">
                                        <label for="name" class="mb-2">Reservation Status</label>
                                        <div class="form-check">
                                            <input class="form-check-input" name="is_rejected" value="0" type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                <i class="fa-solid fa-check"></i> Accept
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" name="is_rejected" value="1" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                <i class="fa-solid fa-xmark"></i> Reject
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="description" class="form-label">Feedback</label>
                                        <textarea class="form-control" id="description" name="feedback" rows="4"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary me-1 my-3">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal Deposit Confirmation -->
    <?php if (($reservation['deposit_at'] != null) && ($reservation['deposit_confirmed_at'] == null)) : ?>
        <div class="modal fade" id="confirmDeposit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Deposit Confirmation</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card-body text-dark">
                            <img src="/media/photos/<?= esc($reservation['deposit_proof']) ?>" class="img-fluid" alt="...">
                            <form class="form form-vertical" action="/dashboard/reservation/deposit/confirm/<?= esc($reservation['id']); ?>" method="post" onsubmit="checkRequired(event)" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="form-group ">
                                        <label for="name" class="mt-2">Is this deposit proof correct?</label>
                                        <div class="form-check">
                                            <input class="form-check-input" name="is_deposit_proof_correct" value="1" type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                <i class="fa-solid fa-check"></i> Correct
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" name="is_deposit_proof_correct" value="0" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                <i class="fa-solid fa-xmark"></i> Incorrect
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label for="description" class="form-label">Feedback</label>
                                        <textarea class="form-control" id="description" name="feedback" rows="4"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary me-1">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal Full Pay Confirmation -->
    <?php if (($reservation['full_paid_at'] != null) && ($reservation['full_paid_confirmed_at'] == null)) : ?>
        <div class="modal fade" id="confirmFullPay" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Full Pay Confirmation</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card-body text-dark">
                            <img src="/media/photos/<?= esc($reservation['full_paid_proof']) ?>" class="img-fluid" alt="...">
                            <form class="form form-vertical" action="/dashboard/reservation/fullPay/confirm/<?= esc($reservation['id']); ?>" method="post" onsubmit="checkRequired(event)" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="form-group ">
                                        <label for="name" class="mt-2">Is this full paid proof correct?</label>
                                        <div class="form-check">
                                            <input class="form-check-input" name="is_full_paid_proof_correct" value="1" type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                <i class="fa-solid fa-check"></i> Correct
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" name="is_full_paid_proof_correct" value="0" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                <i class="fa-solid fa-xmark"></i> Incorrect
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label for="description" class="form-label">Feedback</label>
                                        <textarea class="form-control" id="description" name="feedback" rows="4"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary me-1">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


</section>

<?= $this->endSection() ?>
<?= $this->section('javascript') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDone(reservation_id, deposit) {
        Swal.fire({
            title: "Do you want to finish reservation?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "/web/reservation/finish/" + reservation_id + "/" + deposit;
            }
        });
    }
</script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-media-preview@1.0.11/dist/filepond-plugin-media-preview.min.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
<script src="<?= base_url('assets/js/extensions/form-element-select.js'); ?>"></script>
<script>
    function confirmCancelReservation(reservation_id) {
        Swal.fire({
            title: "Do you want to cancel reservation?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "/web/reservation/cancel/" + reservation_id;
            }
        });
    }
</script>
<script>
    FilePond.registerPlugin(
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType,
        FilePondPluginImageExifOrientation,
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginMediaPreview,
    );

    // Get a reference to the file input element
    const photo = document.querySelector('input[id="gallery"]');
    const video = document.querySelector('input[id="video"]');

    // Create a FilePond instance
    const pond = FilePond.create(photo, {
        maxFileSize: '1920MB',
        maxTotalFileSize: '1920MB',
        imageResizeTargetHeight: 720,
        imageResizeUpscale: false,
        credits: false,
    });
    const vidPond = FilePond.create(video, {
        maxFileSize: '1920MB',
        maxTotalFileSize: '1920MB',
        credits: false,
    })

    pond.setOptions({
        server: {
            timeout: 3600000,
            process: {
                url: '/upload/photo',
                onload: (response) => {
                    console.log("processed:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
            revert: {
                url: '/upload/photo',
                onload: (response) => {
                    console.log("reverted:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
        }
    });
    vidPond.setOptions({
        server: {
            timeout: 86400000,
            process: {
                url: '/upload/video',
                onload: (response) => {
                    console.log("processed:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
            revert: {
                url: '/upload/video',
                onload: (response) => {
                    console.log("reverted:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
        }
    });
</script>
<?= $this->endSection() ?>