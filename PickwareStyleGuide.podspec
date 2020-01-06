Pod::Spec.new do |s|
    # Metadata
    s.name = 'PickwareStyleGuide'
    s.version= '0.0.1'
    s.summary= 'Swift coding conventions used at Pickware'
    s.homepage = 'https://github.com/VIISON/style-guide/'
    s.authors = {
        'Jannik Jochem' => 'jannik.jochem@pickware.com',
        'Sven Münnich' => 'sven.muennich@pickware.com'
    }
    s.license = 'Copyright (c) Pickware GmbH. All rights reserved.'
    s.source = { :git => 'https://github.com/VIISON/style-guide.git', :branch => 'master' }

    # Build settings
    s.swift_version = '5.0'
    s.ios.deployment_target = '9.0'
    s.osx.deployment_target = '10.14'
    s.preserve_paths = 'swift/*.{swift,yml}'
    s.dependency 'SwiftLint', '~> 0.36.0'
end
