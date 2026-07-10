<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public function rendering($view)
    {
        $view->title('Inicio - Abastos Los Trinis')
             ->layoutData([
                 'title' => 'Inicio - Abastos Los Trinis',
                 'description' => 'Abastos Los Trinis - Tu supermercado en línea de confianza. Encuentra víveres, charcutería, bebidas, productos de limpieza y más con entrega a domicilio.',
                 'og_image' => asset('images/logo.png'),
             ]);
    }
};
?>

<div>
    <!-- Hero Slider -->
    <livewire:store.partials.hero-slider />

    <!-- Categories -->
    <livewire:store.partials.categories-grid />

    <!-- Product Catalog -->
    <livewire:store.catalog :show-breadcrumbs="false" />

    <!-- Sale Banner -->
    <livewire:store.partials.sale-banner />

    <!-- New Arrivals -->
    <livewire:store.partials.new-arrivals />

    <!-- Recently Viewed -->
    <livewire:store.partials.recently-viewed-bar />

    <!-- Brands -->
    <livewire:store.partials.brands-showcase />

    <!-- Newsletter -->
    <livewire:store.partials.newsletter-banner />

    <!-- Trust Badges -->
    <livewire:store.partials.trust-badges />
</div>

@push('structured-data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Store",
  "name": "Abastos Los Trinis",
  "image": "{{ asset('images/logo.png') }}",
  "@id": "{{ url('/') }}",
  "url": "{{ url('/') }}",
  "telephone": "+584120000000",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Av. Principal Los Trinis",
    "addressLocality": "San Carlos",
    "addressRegion": "Cojedes",
    "postalCode": "2201",
    "addressCountry": "VE"
  },
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": [
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday",
      "Sunday"
    ],
    "opens": "08:00",
    "closes": "21:00"
  }
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Abastos Los Trinis",
  "url": "{{ url('/') }}",
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "{{ url('/catalogo') }}?search={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
</script>
@endpush
