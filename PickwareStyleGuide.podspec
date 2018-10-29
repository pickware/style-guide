Pod::Spec.new do |s|
  s.name         = "PickwareStyleGuide"
  s.version      = "0.0.1"
  s.summary      = "Swift coding conventions used at Pickware"
  s.description  = "Swift coding conventions used at Pickware"
  s.homepage     = "https://github.com/VIISON/style-guide/"
  s.authors            = {
    "Jannik Jochem" => "jannik.jochem@pickware.com",
    "Sven Münnich" => "sven.muennich@pickware.com"
  }
  s.source       = { :git => "https://github.com/VIISON/style-guide.git", :branch => "fixpunkt/add-swiftlint" }
  s.resources = "swift/*"

  s.dependency 'SwiftLint', '~> 0.27.0'
end
