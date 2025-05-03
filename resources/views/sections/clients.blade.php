<section id="clients" class="clients section light-background">
    <div class="container" data-aos="fade-up">
      <div class="row gy-4">
        @foreach([1, 2, 3, 4, 5, 6] as $client)
        <div class="col-xl-2 col-md-3 col-6 client-logo">
          <img src="{{ asset('assets/img/clients/client-'.$client.'.png') }}" class="img-fluid" alt="Cliente {{ $client }}">
        </div>
        @endforeach
      </div>
    </div>
  </section>