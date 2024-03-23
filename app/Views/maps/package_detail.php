<?= $this->extend('maps/main'); ?>

<?= $this->section('content') ?>

<section class="section">
    <div class="row">
        <div class="col">
            <a href="/web/homestay/detail/<?= esc($data['homestay_id']); ?>" class="btn btn-primary ms-3 mt-3 mb-3"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="row">
        <script>
            currentUrl = '<?= current_url(); ?>';
        </script>
        <!-- Object Detail Information -->
        <div class="col-md-7 col-12">
            <div class="card text-dark">
                <div class="card-header">
                    <h4 class="card-title">Package Information</h4>
                </div>
                <div class="card-body">
                    <?php $i = 0; ?>
                    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
                        <ol class="carousel-indicators">
                            <?php foreach ($data['gallery'] as $item) : ?>
                                <li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="<?= esc($i); ?>" class="<?= ($i == 0) ? 'active' : ''; ?>"></li>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </ol>
                        <div class="carousel-inner">
                            <?php $i = 0; ?>
                            <?php foreach ($data['gallery'] as $item) : ?>
                                <div class="carousel-item<?= ($i == 0) ? ' active' : ''; ?>">
                                    <a>
                                        <img src="<?= base_url('media/photos/' . esc($item)); ?>" class="d-block w-100" alt="<?= esc($data['name']); ?>" style="height:400px; object-fit: cover;">
                                    </a>
                                </div>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </div>
                        <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </a>
                    </div>
                    <div class="col table-responsive mt-3">
                        <table class="table table-borderless text-dark">
                            <tbody>
                                <tr>
                                    <td class="fw-bold">ID</td>
                                    <td><?= esc($data['id']); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Name</td>
                                    <td><?= esc($data['name']); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Rating</td>
                                    <td>
                                        <?php
                                        for ($i = 0; $i < (int)esc($data['avg_rating']); $i++) {
                                        ?>
                                            <i name="rating" class="fas fa-star text-warning" aria-hidden="true"></i>
                                        <?php
                                        }
                                        ?>
                                        <?php
                                        for ($i = 0; $i < (5 - (int)esc($data['avg_rating'])); $i++) {
                                        ?>
                                            <i name="rating" class="far fa-star" aria-hidden="true"></i>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Minimum Capacity</td>
                                    <td><?= esc($data['min_capacity']); ?> people</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Price</td>
                                    <td><?= esc("Rp " . number_format($data['price'], 0, ',', '.')); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Description</td>
                                    <td><?= esc($data['description']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="ms-2 my-3 ">
                            <span class="fw-bold">Service Included</span>
                            <?php if (!empty($list_service)) : ?>
                                <?php foreach ($list_service as $service) : ?>
                                    <?php if ($service['status'] == '1') : ?>
                                        <li><?= esc($service['name']); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="ms-2">
                            <span class="fw-bold">Service Excluded</span>
                            <?php if (!empty($list_service)) : ?>
                                <?php foreach ($list_service as $service) : ?>
                                    <?php if ($service['status'] == '0') : ?>
                                        <li><?= esc($service['name']); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="accordion mt-3" id="accordionPanelsStayOpenExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                        Package Activity
                                    </button>
                                </h2>
                                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                                    <div class="accordion-body">
                                        <?php foreach ($list_day as $day) : ?>
                                            <div class="mt-3">
                                                <span class="fw-bold">Day <?= esc($day['day']); ?></span>
                                                <ol type="1">
                                                    <?php foreach ($list_activity as $activity) : ?>
                                                        <?php if ($activity['day'] == $day['day']) : ?>
                                                            <li><?= esc($activity['object_name']); ?>
                                                                <?php if ($activity['description'] != null) : ?>
                                                                    <?= esc(' : ' . $activity['description']); ?>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </ol>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    const myModal = document.getElementById('videoModal');
    const videoSrc = document.getElementById('video-play').getAttribute('data-src');

    myModal.addEventListener('shown.bs.modal', () => {
        console.log(videoSrc);
        document.getElementById('video').setAttribute('src', videoSrc);
    });
    myModal.addEventListener('hide.bs.modal', () => {
        document.getElementById('video').setAttribute('src', '');
    });
</script>
<?= $this->endSection() ?>