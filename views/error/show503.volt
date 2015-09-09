<style>
	body,html {
			height: 100%;
		}
		body {
			font-family: Arial, Helvetica, sans-serif;
			background-color: #CCC !important;
			color: #444 !important;
			margin: 0;
		}
		.row-outer {
			margin: 0 auto;
			padding: 0 1.5em;
			max-width: 640epx;
			display: table;
			height:100%;
			max-width: 100% !important;
		}
		.inner {
			display: table-cell;
			vertical-align: middle;
			max-width: 100% !important;
		}
		.text-center {
			text-align: center;
		}
		h5 {
			font-weight: bold !important;
			animation-delay: 3s !important;
			font-size: 1.125rem;
			margin: 0.3em 0;

		}
		p {
			font-size: 0.75em !important;
			padding-top: 0.5em !important;
		}
		img {
			width: 100%;
		}


		.animated {
			-webkit-animation-duration: 1s;
			animation-duration: 1s;
			animation-delay: 0.5s !important;
			-webkit-animation-fill-mode: both;
			animation-fill-mode: both;
		}

		@-webkit-keyframes bounceInDown {
			0%, 60%, 75%, 90%, 100% {
				-webkit-transition-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
				transition-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
			}

			0% {
				opacity: 0;
				-webkit-transform: translate3d(0, -3000px, 0);
				transform: translate3d(0, -3000px, 0);
			}

			60% {
				opacity: 1;
				-webkit-transform: translate3d(0, 25px, 0);
				transform: translate3d(0, 25px, 0);
			}

			75% {
				-webkit-transform: translate3d(0, -10px, 0);
				transform: translate3d(0, -10px, 0);
			}

			90% {
				-webkit-transform: translate3d(0, 5px, 0);
				transform: translate3d(0, 5px, 0);
			}

			100% {
				-webkit-transform: none;
				transform: none;
			}
		}

		@keyframes bounceInDown {
			0%, 60%, 75%, 90%, 100% {
				-webkit-transition-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
				transition-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
			}

			0% {
				opacity: 0;
				-webkit-transform: translate3d(0, -3000px, 0);
				-ms-transform: translate3d(0, -3000px, 0);
				transform: translate3d(0, -3000px, 0);
			}

			60% {
				opacity: 1;
				-webkit-transform: translate3d(0, 25px, 0);
				-ms-transform: translate3d(0, 25px, 0);
				transform: translate3d(0, 25px, 0);
			}

			75% {
				-webkit-transform: translate3d(0, -10px, 0);
				-ms-transform: translate3d(0, -10px, 0);
				transform: translate3d(0, -10px, 0);
			}

			90% {
				-webkit-transform: translate3d(0, 5px, 0);
				-ms-transform: translate3d(0, 5px, 0);
				transform: translate3d(0, 5px, 0);
			}

			100% {
				-webkit-transform: none;
				-ms-transform: none;
				transform: none;
			}
		}

		.bounceInDown {
			-webkit-animation-name: bounceInDown;
			animation-name: bounceInDown;

		}
		@-webkit-keyframes fadeInLeft {
		  0% {
		    opacity: 0;
		    -webkit-transform: translate3d(-30%, 0, 0);
		    transform: translate3d(-30%, 0, 0);
		  }

		  100% {
		    opacity: 1;
		    -webkit-transform: none;
		    transform: none;
		  }
		}

		@keyframes fadeInLeft {
		  0% {
		    opacity: 0;
		    -webkit-transform: translate3d(-30%, 0, 0);
		    -ms-transform: translate3d(-30%, 0, 0);
		    transform: translate3d(-30%, 0, 0);
		  }

		  100% {
		    opacity: 1;
		    -webkit-transform: none;
		    -ms-transform: none;
		    transform: none;
		  }
		}

		.fadeInLeft {
		  -webkit-animation-name: fadeInLeft;
		  animation-name: fadeInLeft;
		}

		@-webkit-keyframes fadeIn {
		  0% {
		    opacity: 0;
		  }

		  100% {
		    opacity: 1;
		  }
		}

		@keyframes fadeIn {
		  0% {
		    opacity: 0;
		  }

		  100% {
		    opacity: 1;
		  }
		}

		.fadeIn {
		  -webkit-animation-name: fadeIn;
		  animation-name: fadeIn;
		}

</style>
<div class="row-outer">
	<div class="inner text-center">
		<img id="logo" class="animated" alt="Phalcana" src="data:image/png;base64,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"/>


		<h5  id="install" class="animated">This site is currently down for maintainance</h5>
	</div>
</div>

<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (event) {
			document.getElement
			var d = document.getElementById('logo');
			d.className = d.className + " bounceInDown";
			var e = document.getElementById('install');
			e.style.opacity = 0;

			setTimeout(function () {
				e.className = e.className + " fadeInLeft";
			},1000);
		});
</script>

