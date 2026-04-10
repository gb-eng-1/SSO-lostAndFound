@extends('layouts.student')

@section('title', 'Help and Support')

@push('styles')
  <link rel="stylesheet" href="{{ asset('STUDENT/HelpSupport.css') }}?v=3">
@endpush

@section('content')

  <div class="hs-wrap">
    <h1 class="hs-page-title">Help and Support</h1>

    <div class="hs-grid">
      <div class="hs-col-left">
        @include('partials.student-how-to-report-lost')

        <div class="hs-card">
          <h2 class="hs-card-title">What to do with lost ATMs?</h2>
          <p class="hs-atm-label">If you lost an ATM or debit card:</p>
          <ul class="hs-atm-list">
            <li>Check your immediate surroundings and retrace your steps where you last used the card.</li>
            <li>
              Contact your bank immediately through their 24/7 helpline or mobile app to block the card and prevent unauthorized use.
              <ul class="hs-atm-sublist">
                <li>Request a card replacement if needed.</li>
                <li>Monitor your account for suspicious transactions.</li>
              </ul>
            </li>
          </ul>
        </div>
      </div>

      <div class="hs-col-right">
        <div class="hs-card hs-card-map">
          <div class="hs-map-embed">
            <iframe
              title="University of Batangas, Batangas City"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              src="https://www.google.com/maps?q=University+of+Batangas+Batangas+City+Philippines&output=embed&z=15">
            </iframe>
          </div>
          <div class="hs-office-info">
            <div class="hs-office-row">
              <span class="hs-office-label">Building</span>
              <div class="hs-office-value">
                <span>Security Office, Ground Floor, Gate</span>
              </div>
            </div>
            <div class="hs-office-row">
              <span class="hs-office-label">Hours</span>
              <div class="hs-office-value">
                <span>Monday&ndash;Friday, 9:00 AM &ndash; 5:00 PM</span>
                <span>Saturday, 9:00 AM &ndash; 12:00 PM</span>
              </div>
            </div>
            <div class="hs-office-open">
              <span class="hs-open-dot" aria-hidden="true"></span>
              <span class="hs-open-text">Open Now</span>
              <a class="hs-directions-btn"
                 href="https://www.google.com/maps/dir/?api=1&amp;destination=University+of+Batangas%2C+Batangas+City%2C+Philippines"
                 target="_blank"
                 rel="noopener noreferrer">Get Directions</a>
            </div>
          </div>
        </div>

        <div class="hs-card hs-card-bring">
          <h2 class="hs-card-title">What to Bring to Claim</h2>
          <div class="hs-bring-row">
            <div class="hs-bring-item">
              <i class="fa-regular fa-square hs-bring-icon" aria-hidden="true"></i>
              <span class="hs-bring-label">Student ID</span>
            </div>
            <div class="hs-bring-item">
              <i class="fa-regular fa-square hs-bring-icon" aria-hidden="true"></i>
              <span class="hs-bring-label">Ticket ID</span>
            </div>
          </div>
        </div>

        <div class="hs-card hs-card-contact">
          <div class="hs-contact-item">
            <div class="hs-contact-icon"><i class="fa-solid fa-phone"></i></div>
            <div class="hs-contact-body">
              <span class="hs-contact-label">Call Office</span>
              <a class="hs-contact-value" href="tel:0737866675">073-7866-675</a>
            </div>
          </div>
          <div class="hs-contact-divider" aria-hidden="true"></div>
          <div class="hs-contact-item">
            <div class="hs-contact-icon"><i class="fa-solid fa-envelope"></i></div>
            <div class="hs-contact-body">
              <span class="hs-contact-label">Email Support</span>
              <a class="hs-contact-value" href="mailto:ssd@ub.edu.ph">ssd@ub.edu.ph</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
